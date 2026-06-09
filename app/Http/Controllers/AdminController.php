<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\FormDet;
use App\Models\Module;
use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class AdminController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = (bool) $user->is_admin;
        $canViewAllRegistros = $isAdmin || $user->can(Module::REGISTRO_VIEW_ALL_PERMISSION);

        $registros = Registro::with(['user', 'almacen', 'profesion', 'procesamientoHistorico.formDet'])
            ->when(!$canViewAllRegistros, function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('correo', 'like', "%{$search}%")
                        ->orWhere('fecha_envio', 'like', "%{$search}%")
                        ->orWhereHas('profesion', fn ($p) => $p->where('nombre_profesion', 'like', "%{$search}%"))
                        ->orWhereHas('almacen', fn ($e) => $e->where('nombre_ipress', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('registro.index', compact('registros'));
    }

    public function dashboard(Request $request)
    {
        $mesesDisponibles = FormDet::query()
            ->whereNotNull('ANNOMES')
            ->where('ANNOMES', '<>', '')
            ->distinct()
            ->orderByDesc('ANNOMES')
            ->pluck('ANNOMES');

        $annomes = $request->input('annomes', $mesesDisponibles->first());

        if (! $annomes) {
            $annomes = now()->format('Ym');
        }

        $almacenesBase = Almacen::query()
            ->where('para_descarga_siga', 'SI');

        $totalAlmacenes = (clone $almacenesBase)->count();

        $codigosConEnvio = DB::table('form_det')
            ->select('CODIGO_PRE')
            ->where('ANNOMES', $annomes)
            ->whereNotNull('CODIGO_PRE')
            ->where('CODIGO_PRE', '<>', '')
            ->distinct();

        $almacenesEnviaron = (clone $almacenesBase)
            ->select('id', 'cod_ipress', 'nombre_ipress')
            ->whereIn('cod_ipress', $codigosConEnvio)
            ->orderBy('nombre_ipress')
            ->get();

        $almacenesPendientes = (clone $almacenesBase)
            ->select('id', 'cod_ipress', 'nombre_ipress')
            ->whereNotIn('cod_ipress', $codigosConEnvio)
            ->orderBy('nombre_ipress')
            ->get();

        $enviaronEsteMes = $almacenesEnviaron->count();

        $porcentaje = $totalAlmacenes > 0 ? round(($enviaronEsteMes / $totalAlmacenes) * 100, 2) : 0;

        // === Gráfico de requerimientos por almacén ===
        $requerimientos = \App\Models\Requerimiento::with('almacen')
            ->whereMonth('fecha_registro', now()->month)
            ->whereYear('fecha_registro', now()->year)
            ->selectRaw('almacen_id, SUM(req_final) as total_requerimiento')
            ->groupBy('almacen_id')
            ->get();

        // Armamos etiquetas (cod_ipress + nombre_ipress)
        $labels = [];
        $data = [];
        foreach ($requerimientos as $r) {
            if ($r->almacen) {
                $labels[] = $r->almacen->cod_ipress . ' - ' . $r->almacen->nombre_ipress;
                $data[] = $r->total_requerimiento;
            }
        }

        return view('admin.dashboard', compact(
            'mesesDisponibles',
            'annomes',
            'totalAlmacenes',
            'enviaronEsteMes',
            'porcentaje',
            'labels',
            'data',
            'almacenesEnviaron',
            'almacenesPendientes'
        ));
    }

    public function dashboardDetalleAlmacen(Request $request)
    {
        $validated = $request->validate([
            'annomes' => ['required', 'string', 'max:20'],
            'cod_ipress' => ['required', 'string', 'max:50'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        $annomes = $validated['annomes'];
        $codIpress = trim($validated['cod_ipress']);
        $limit = (int) ($validated['limit'] ?? 150);

        $detalle = DB::table('form_det as fd')
            ->select(
                'fd.CODIGO_PRE',
                'fd.CODIGO_MED',
                'fd.SIS',
                'fd.SALDO',
                'fd.STOCK_FIN'
            )
            ->where('fd.ANNOMES', $annomes)
            ->where('fd.CODIGO_PRE', $codIpress)
            ->orderBy('fd.CODIGO_MED')
            ->limit($limit + 1)
            ->get();

        $hasMore = $detalle->count() > $limit;
        $rows = $hasMore ? $detalle->take($limit)->values() : $detalle;

        return response()->json([
            'cod_ipress' => $codIpress,
            'annomes' => $annomes,
            'has_more' => $hasMore,
            'limit' => $limit,
            'rows' => $rows,
        ]);
    }

    /**
     * Ruta temporal para ejecutar backfill de registro_id en históricos.
     * - Simulación por defecto.
     * - Aplica cambios solo cuando se envía force=1.
     */
    public function ejecutarBackfillRegistroId(Request $request)
    {
        $windowHours = max(1, (int) $request->query('window_hours', 24));
        $limit = max(0, (int) $request->query('limit', 0));
        $force = (int) $request->query('force', 0) === 1;

        $params = [
            '--window-hours' => $windowHours,
            '--limit' => $limit,
        ];

        if ($force) {
            $params['--force'] = true;
        }

        Artisan::call('app:backfill-registro-id', $params);
        $salida = trim(Artisan::output());

        return redirect()
            ->route('historicos.index')
            ->with(
                $force ? 'success' : 'warning',
                ($force ? 'Backfill ejecutado en modo APLICAR.' : 'Backfill ejecutado en modo SIMULACION.')
                . '<br><pre style="white-space: pre-wrap; margin:0;">' . e($salida) . '</pre>'
            );
    }



}

