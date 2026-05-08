<?php

namespace App\Console\Commands;

use App\Models\ProcesamientoHistorico;
use App\Models\Registro;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class BackfillProcesamientosRegistroId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-registro-id
                            {--force : Aplica cambios en base de datos}
                            {--window-hours=24 : Ventana máxima (horas) para emparejar por fecha}
                            {--limit=0 : Limitar cantidad de históricos a revisar (0 = todos)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rellena registro_id en procesamientos_historicos antiguos usando logs y reglas de respaldo.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!Schema::hasColumn('procesamientos_historicos', 'registro_id')) {
            $this->error("La columna 'registro_id' no existe en 'procesamientos_historicos'. Ejecuta migraciones primero.");
            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $windowHours = max(1, (int) $this->option('window-hours'));
        $windowSeconds = $windowHours * 3600;
        $limit = max(0, (int) $this->option('limit'));

        $query = ProcesamientoHistorico::query()
            ->whereNull('registro_id')
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $historicos = $query->get();

        if ($historicos->isEmpty()) {
            $this->info('No hay registros históricos pendientes de completar.');
            return self::SUCCESS;
        }

        $linkedRegistroIds = ProcesamientoHistorico::query()
            ->whereNotNull('registro_id')
            ->pluck('registro_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->line('Modo: ' . ($force ? 'APLICAR CAMBIOS' : 'SIMULACION (dry-run)'));
        $this->line("Históricos pendientes: {$historicos->count()}");
        $this->line("Ventana máxima: {$windowHours} horas");
        $this->newLine();

        $updated = 0;
        $skipped = 0;

        $logMatches = $this->buildLogMatches($historicos, $windowSeconds);

        foreach ($historicos as $historico) {
            $candidato = null;
            $source = null;

            // Estrategia 1 (más confiable): logs con "Registro ID: X".
            if (isset($logMatches[$historico->id])) {
                $registroIdFromLog = (int) $logMatches[$historico->id]['registro_id'];
                $diffFromLog = (int) $logMatches[$historico->id]['diff_seconds'];

                $registroFromLog = Registro::query()
                    ->where('id', $registroIdFromLog)
                    ->whereNotIn('id', $linkedRegistroIds)
                    ->first();

                if ($registroFromLog) {
                    $registroFromLog->diff_seconds = $diffFromLog;
                    $candidato = $registroFromLog;
                    $source = 'LOG';
                }
            }

            // Estrategia 1 (principal): inferir almacén desde form_det.CODIGO_PRE.
            // El procesamiento puede hacerlo un usuario distinto al que subió el archivo.
            if (!$candidato) {
                $codigosPre = DB::table('form_det')
                    ->where('procesamiento_id', $historico->id)
                    ->whereNotNull('CODIGO_PRE')
                    ->distinct()
                    ->pluck('CODIGO_PRE')
                    ->map(fn ($v) => trim((string) $v))
                    ->filter()
                    ->values();

                if ($codigosPre->count() === 1) {
                    $codigoPre = $codigosPre->first();

                    $almacenIds = DB::table('almacenes')
                        ->where('cod_ipress', $codigoPre)
                        ->pluck('id');

                    if ($almacenIds->isNotEmpty()) {
                        $candidato = Registro::query()
                            ->whereIn('almacen_id', $almacenIds->all())
                            ->where('procesado', true)
                            ->whereNotIn('id', $linkedRegistroIds)
                            ->select('*')
                            ->selectRaw(
                                'ABS(TIMESTAMPDIFF(SECOND, created_at, ?)) as diff_seconds',
                                [$historico->fecha_ejecucion]
                            )
                            ->orderBy('diff_seconds')
                            ->orderBy('id')
                            ->first();
                        $source = $candidato ? 'CODIGO_PRE' : null;
                    }
                }
            }

            // Estrategia 3 (fallback): mismo usuario, por cercanía temporal.
            if (!$candidato) {
                $candidato = Registro::query()
                    ->where('user_id', $historico->user_id)
                    ->where('procesado', true)
                    ->whereNotIn('id', $linkedRegistroIds)
                    ->select('*')
                    ->selectRaw(
                        'ABS(TIMESTAMPDIFF(SECOND, created_at, ?)) as diff_seconds',
                        [$historico->fecha_ejecucion]
                    )
                    ->orderBy('diff_seconds')
                    ->orderBy('id')
                    ->first();
                $source = $candidato ? 'USER_FALLBACK' : null;
            }

            if (!$candidato || (int) $candidato->diff_seconds > $windowSeconds) {
                $skipped++;
                $this->warn("Histórico {$historico->id}: sin match confiable");
                continue;
            }

            $msg = "Histórico {$historico->id} -> Registro {$candidato->id} (diff {$candidato->diff_seconds}s, via {$source})";

            if ($force) {
                DB::transaction(function () use ($historico, $candidato) {
                    $historico->update(['registro_id' => $candidato->id]);
                });
            }

            $linkedRegistroIds[] = (int) $candidato->id;
            $updated++;
            $this->info($msg);
        }

        $this->newLine();
        $this->line("Completados: {$updated}");
        $this->line("Omitidos: {$skipped}");

        if (!$force) {
            $this->comment("Para aplicar cambios ejecuta: php artisan app:backfill-registro-id --force");
        }

        return self::SUCCESS;
    }

    /**
     * Construye matches desde logs de Laravel:
     * [historico_id => ['registro_id' => int, 'diff_seconds' => int]]
     */
    private function buildLogMatches($historicos, int $windowSeconds): array
    {
        $logDir = storage_path('logs');
        if (!File::isDirectory($logDir)) {
            return [];
        }

        $historicosByDate = [];
        foreach ($historicos as $historico) {
            $date = Carbon::parse($historico->fecha_ejecucion)->format('Y-m-d');
            $historicosByDate[$date][] = $historico;
        }

        $matches = [];
        $usedRegistroIds = [];

        foreach ($historicosByDate as $date => $histList) {
            $logFile = $logDir . DIRECTORY_SEPARATOR . "laravel-{$date}.log";
            if (!File::exists($logFile)) {
                continue;
            }

            $content = File::get($logFile);
            $lines = preg_split("/\r\n|\n|\r/", $content);
            $events = [];

            foreach ($lines as $line) {
                if (!preg_match('/^\[(.*?)\]\s+.*?:\s+(.*)$/', $line, $lineMatch)) {
                    continue;
                }

                $timestampRaw = $lineMatch[1];
                $message = $lineMatch[2];

                if (str_contains($message, 'Registro ID:')) {
                    if (preg_match('/Registro ID:\s*(\d+)/', $message, $idMatch)) {
                        try {
                            $events[] = [
                                'ts' => Carbon::parse($timestampRaw),
                                'registro_id' => (int) $idMatch[1],
                            ];
                        } catch (\Throwable $e) {
                            // Ignorar líneas con timestamp inválido.
                        }
                    }
                }
            }

            foreach ($histList as $historico) {
                $targetTs = Carbon::parse($historico->fecha_ejecucion);
                $best = null;

                foreach ($events as $event) {
                    if (in_array($event['registro_id'], $usedRegistroIds, true)) {
                        continue;
                    }

                    $diff = abs($event['ts']->diffInSeconds($targetTs));
                    if ($diff > $windowSeconds) {
                        continue;
                    }

                    if (!$best || $diff < $best['diff_seconds']) {
                        $best = [
                            'registro_id' => $event['registro_id'],
                            'diff_seconds' => $diff,
                        ];
                    }
                }

                if ($best) {
                    $matches[$historico->id] = $best;
                    $usedRegistroIds[] = $best['registro_id'];
                }
            }
        }

        return $matches;
    }
}

