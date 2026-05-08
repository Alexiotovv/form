<?php

namespace App\Console\Commands;

use App\Models\ProcesamientoHistorico;
use App\Models\Registro;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
    protected $description = 'Rellena registro_id en procesamientos_historicos antiguos usando emparejamiento por usuario + proximidad de fecha.';

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

        foreach ($historicos as $historico) {
            // Candidato: mismo usuario, ya procesado, no enlazado aún.
            // Elegimos el más cercano por created_at respecto a fecha_ejecucion.
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

            if (!$candidato || (int) $candidato->diff_seconds > $windowSeconds) {
                $skipped++;
                $this->warn("Histórico {$historico->id}: sin match confiable");
                continue;
            }

            $msg = "Histórico {$historico->id} -> Registro {$candidato->id} (diff {$candidato->diff_seconds}s)";

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
}

