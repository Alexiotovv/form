<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ResumenController extends Controller
{
    public function index(Request $request)
    {
        $fechaManual = $request->input('fin_mes') ?? date('Y-m-t');
        if (!strtotime($fechaManual)) {
            $fechaManual = date('Y-m-t');
        }

        $filtros = [
            'red' => $request->input('red', []),
            'microred' => $request->input('microred', []),
            'tipo_establecimiento' => $request->input('tipo_establecimiento', []),
            'tipo_abastecimiento' => $request->input('tipo_abastecimiento', []),
            'tipo_prod' => $request->input('tipo_prod', []),
            'peti2023' => $request->input('peti2023', []),
            'tip_sum' => $request->input('tip_sum', []),
            'lista_1' => $request->input('lista_1', []),
        ];

        foreach ($filtros as $k => $v) {
            if (!is_array($v)) {
                $filtros[$k] = [$v];
            }
        }

        $opcionesFiltros = $this->obtenerOpcionesFiltros($filtros);
        $queryRedes = $this->queryAlmacenesPermitidos();
        $queryRedes = $this->filtrarQueryPorCampo($queryRedes, $filtros['microred'], 'microred');
        $queryRedes = $this->filtrarQueryPorCampo($queryRedes, $filtros['tipo_establecimiento'], 'tipo_establecimiento');
        if (!empty($filtros['red'])) {
            $queryRedes = $this->filtrarQueryPorCampo($queryRedes, $filtros['red'], 'red');
        }

        $redes = $queryRedes
            ->whereNotNull('red')
            ->where('red', '<>', '')
            ->distinct()
            ->orderBy('red')
            ->pluck('red');

        $resumenGeneral = [
            'sin_consumo' => 0,
            'desabastecido' => 0,
            'substock' => 0,
            'normostock' => 0,
            'sobrestock' => 0,
            'sin_rotacion' => 0,
            'por_vencer' => 0,
            'vencidos' => 0,
            'total' => 0,
            'disponibilidad' => 0,
            'nivel' => 'BAJO',
        ];

        return view('resumen.index', [
            'fechaManual' => $fechaManual,
            'filtros' => $filtros,
            'opcionesFiltros' => $opcionesFiltros,
            'resumenGeneral' => $resumenGeneral,
            'redes' => $redes,
            'totalRegistros' => 0,
        ]);
    }

    public function redData(Request $request): JsonResponse
    {
        set_time_limit(180);

        $request->validate([
            'red' => 'required|string',
            'fin_mes' => 'required|date',
        ]);

        $filtros = $this->normalizarFiltros($request);
        $red = $request->input('red');
        $fechaManual = $request->input('fin_mes');

        $query = $this->queryAlmacenesPermitidos()->where('red', $red);
        $query = $this->filtrarQueryPorCampo($query, $filtros['microred'], 'microred');
        $query = $this->filtrarQueryPorCampo($query, $filtros['tipo_establecimiento'], 'tipo_establecimiento');

        $codigos = $query
            ->whereNotNull('cod_ipress')
            ->where('cod_ipress', '<>', '')
            ->distinct()
            ->pluck('cod_ipress');

        $registros = $this->obtenerRegistrosPorCodigos($codigos, $fechaManual);
        $registros = $this->aplicarFiltrosColeccion($registros, $filtros);

        $microredes = $registros
            ->groupBy(fn ($item) => trim((string) ($item->microred ?? 'SIN MICRORED')) ?: 'SIN MICRORED')
            ->map(function (Collection $items, string $microred) {
                return [
                    'microred' => $microred,
                    'totales' => $this->calcularTotalesEstado($items),
                ];
            })
            ->sortBy('microred', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return response()->json([
            'totales' => $this->calcularTotalesEstado($registros),
            'microredes' => $microredes,
        ]);
    }

    public function microredData(Request $request): JsonResponse
    {
        set_time_limit(180);

        $request->validate([
            'red' => 'required|string',
            'microred' => 'required|string',
            'fin_mes' => 'required|date',
        ]);

        $filtros = $this->normalizarFiltros($request);
        $red = $request->input('red');
        $microred = $request->input('microred');
        $fechaManual = $request->input('fin_mes');

        $query = $this->queryAlmacenesPermitidos()
            ->where('red', $red)
            ->where('microred', $microred);
        $query = $this->filtrarQueryPorCampo($query, $filtros['tipo_establecimiento'], 'tipo_establecimiento');

        $codigos = $query
            ->whereNotNull('cod_ipress')
            ->where('cod_ipress', '<>', '')
            ->distinct()
            ->pluck('cod_ipress');

        $registros = $this->obtenerRegistrosPorCodigos($codigos, $fechaManual);
        $registros = $this->aplicarFiltrosColeccion($registros, $filtros);

        $ipress = $registros
            ->groupBy(fn ($item) => (trim((string) ($item->cod_ipress ?? '')) ?: 'SIN CODIGO') . '|' . (trim((string) ($item->nombre_ipress ?? 'SIN IPRESS')) ?: 'SIN IPRESS'))
            ->map(function (Collection $items, string $clave) {
                [$codigo, $nombre] = explode('|', $clave, 2);

                return [
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'totales' => $this->calcularTotalesEstado($items),
                ];
            })
            ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return response()->json([
            'totales' => $this->calcularTotalesEstado($registros),
            'ipress' => $ipress,
        ]);
    }

    private function obtenerRegistrosMatriz(string $codigoPre, ?string $codigoSismed, string $fechaManual): Collection
    {
        $fechaObj = Carbon::parse($fechaManual);
        $fechaReferencia = $fechaObj->format('Y-m-d');

        $results = DB::select('CALL sp_obtener_registros_matriz(?, ?, ?)', [
            $codigoPre,
            $codigoSismed,
            $fechaReferencia,
        ]);

        return collect($results);
    }

    private function obtenerRegistrosPorCodigos(Collection $codigos, string $fechaManual): Collection
    {
        $coleccion = collect();

        foreach ($codigos as $codigoPre) {
            $key = 'resumen_sp_' . md5($codigoPre . '|' . $fechaManual);
            $rows = Cache::remember($key, now()->addMinutes(30), function () use ($codigoPre, $fechaManual) {
                return $this->obtenerRegistrosMatriz($codigoPre, null, $fechaManual)->all();
            });

            $coleccion = $coleccion->merge(collect($rows));
        }

        return $coleccion;
    }

    private function normalizarFiltros(Request $request): array
    {
        $filtros = [
            'red' => $request->input('red', []),
            'microred' => $request->input('microred', []),
            'tipo_establecimiento' => $request->input('tipo_establecimiento', []),
            'tipo_abastecimiento' => $request->input('tipo_abastecimiento', []),
            'tipo_prod' => $request->input('tipo_prod', []),
            'peti2023' => $request->input('peti2023', []),
            'tip_sum' => $request->input('tip_sum', []),
            'lista_1' => $request->input('lista_1', []),
        ];

        foreach ($filtros as $k => $v) {
            if (!is_array($v)) {
                $filtros[$k] = [$v];
            }
        }

        return $filtros;
    }

    private function queryAlmacenesPermitidos()
    {
        $query = Almacen::query();
        $user = auth()->user();
        $isSuperAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('superadmin');

        if (!$isSuperAdmin && !(bool) $user->is_admin && $user->almacen_id) {
            $query->where('id', $user->almacen_id);
        }

        return $query;
    }

    private function filtrarQueryPorCampo($query, array $valores, string $campo)
    {
        if (empty($valores)) {
            return $query;
        }

        $incluirNulos = in_array('__NULL__', $valores, true);
        $valoresNormales = array_values(array_filter($valores, static fn ($v) => $v !== '__NULL__' && $v !== ''));

        return $query->where(function ($q) use ($campo, $incluirNulos, $valoresNormales) {
            if (!empty($valoresNormales)) {
                $q->whereIn($campo, $valoresNormales);
            }

            if ($incluirNulos) {
                $q->orWhereNull($campo)->orWhere($campo, '');
            }
        });
    }

    private function aplicarFiltrosColeccion(Collection $coleccion, array $filtros): Collection
    {
        $coleccion = $this->filtrarColeccionPorCampo($coleccion, $filtros['red'], 'red');
        $coleccion = $this->filtrarColeccionPorCampo($coleccion, $filtros['microred'], 'microred');
        $coleccion = $this->filtrarColeccionPorCampo($coleccion, $filtros['tipo_establecimiento'], 'tipo_establecimiento');
        $coleccion = $this->filtrarColeccionPorCampo($coleccion, $filtros['tipo_abastecimiento'], 'tipo_abastecimiento');
        $coleccion = $this->filtrarColeccionPorCampo($coleccion, $filtros['tipo_prod'], 'tipo_prod');
        $coleccion = $this->filtrarColeccionPorCampo($coleccion, $filtros['peti2023'], 'peti2023');
        $coleccion = $this->filtrarColeccionPorCampo($coleccion, $filtros['tip_sum'], 'TIPSUM');
        $coleccion = $this->filtrarColeccionPorCampo($coleccion, $filtros['lista_1'], 'lista_1');

        return $coleccion;
    }

    private function filtrarColeccionPorCampo(Collection $coleccion, array $valoresFiltro, string $campo): Collection
    {
        if (empty($valoresFiltro)) {
            return $coleccion;
        }

        $incluirNulos = in_array('__NULL__', $valoresFiltro, true);
        $valoresNormales = array_values(array_filter($valoresFiltro, static fn ($v) => $v !== '__NULL__' && $v !== ''));

        return $coleccion->filter(function ($item) use ($campo, $incluirNulos, $valoresNormales) {
            $valorCampo = $item->$campo ?? null;
            $esNulo = is_null($valorCampo) || trim((string) $valorCampo) === '';

            if ($incluirNulos && $esNulo) {
                return true;
            }

            return !empty($valoresNormales) && in_array($valorCampo, $valoresNormales, true);
        })->values();
    }

    private function calcularTotalesEstado(Collection $items): array
    {
        $totales = [
            'sin_consumo' => 0,
            'desabastecido' => 0,
            'substock' => 0,
            'normostock' => 0,
            'sobrestock' => 0,
            'sin_rotacion' => 0,
            'por_vencer' => 0,
            'vencidos' => 0,
            'total' => 0,
            'disponibilidad' => 0,
            'nivel' => 'BAJO',
        ];

        foreach ($items as $item) {
            $situacion = strtoupper(trim((string) ($item->situacion_stock ?? '')));
            $vencimiento = strtoupper(trim((string) ($item->sit_fecha_vcmto ?? '')));

            switch ($situacion) {
                case 'SIN CONSUMO':
                    $totales['sin_consumo']++;
                    break;
                case 'DESABASTECIDO':
                    $totales['desabastecido']++;
                    break;
                case 'SUBSTOCK':
                    $totales['substock']++;
                    break;
                case 'NORMOSTOCK':
                    $totales['normostock']++;
                    break;
                case 'SOBRESTOCK':
                    $totales['sobrestock']++;
                    break;
                case 'SIN ROTACION':
                    $totales['sin_rotacion']++;
                    break;
            }

            if ($vencimiento === 'POR VENCER') {
                $totales['por_vencer']++;
            }
            if ($vencimiento === 'VENCIDO') {
                $totales['vencidos']++;
            }
        }

        $totales['total'] = $totales['desabastecido'] + $totales['substock'] + $totales['normostock'] + $totales['sobrestock'];
        $totales['disponibilidad'] = $totales['total'] > 0
            ? round((($totales['normostock'] + $totales['sobrestock']) / $totales['total']) * 100, 1)
            : 0;

        if ($totales['disponibilidad'] >= 80) {
            $totales['nivel'] = 'ALTO';
        } elseif ($totales['disponibilidad'] >= 70) {
            $totales['nivel'] = 'REGULAR';
        } else {
            $totales['nivel'] = 'BAJO';
        }

        return $totales;
    }

    private function obtenerOpcionesFiltros(array $filtros): array
    {
        $almacenesBase = $this->queryAlmacenesPermitidos();

        $redes = (clone $almacenesBase)
            ->whereNotNull('red')
            ->where('red', '<>', '')
            ->distinct()
            ->orderBy('red')
            ->pluck('red');

        $microredQuery = clone $almacenesBase;
        if (!empty($filtros['red'])) {
            $microredQuery = $this->filtrarQueryPorCampo($microredQuery, $filtros['red'], 'red');
        }

        $microredes = $microredQuery
            ->whereNotNull('microred')
            ->where('microred', '<>', '')
            ->distinct()
            ->orderBy('microred')
            ->pluck('microred');

        $tiposEstablecimiento = (clone $almacenesBase)
            ->whereNotNull('tipo_establecimiento')
            ->where('tipo_establecimiento', '<>', '')
            ->distinct()
            ->orderBy('tipo_establecimiento')
            ->pluck('tipo_establecimiento');

        return [
            'redes' => $redes,
            'microredes' => $microredes,
            'tipos_establecimiento' => $tiposEstablecimiento,
            'tipos_abastecimiento' => DB::table('productos')->whereNotNull('tipo_abastecimiento')->where('tipo_abastecimiento', '<>', '')->distinct()->orderBy('tipo_abastecimiento')->pluck('tipo_abastecimiento'),
            'tipos_prod' => DB::table('productos')->whereNotNull('tipo_prod')->where('tipo_prod', '<>', '')->distinct()->orderBy('tipo_prod')->pluck('tipo_prod'),
            'peti2023' => DB::table('productos')->whereNotNull('peti2023')->where('peti2023', '<>', '')->distinct()->orderBy('peti2023')->pluck('peti2023'),
            'tipsum' => DB::table('form_det')->whereNotNull('TIPSUM')->where('TIPSUM', '<>', '')->distinct()->orderBy('TIPSUM')->pluck('TIPSUM'),
            'lista_1' => DB::table('productos')->whereNotNull('lista_1')->where('lista_1', '<>', '')->distinct()->orderBy('lista_1')->pluck('lista_1'),
        ];
    }
}
