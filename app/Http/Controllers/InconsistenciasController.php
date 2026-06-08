<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InconsistenciasController extends Controller
{
    public function index(Request $request)
    {
        // Meses disponibles en form_det
        $mesesDisponibles = DB::table('form_det')
            ->selectRaw('DISTINCT ANNOMES')
            ->orderByDesc('ANNOMES')
            ->pluck('ANNOMES');

        $annomes = $request->input('annomes', $mesesDisponibles->first());

        $resumen = collect();
        $detalle = collect();

        if ($annomes) {
            // Agrupado por IPRESS sobre todos los registros del periodo.
            // Solo muestra IPRESS cuyo total SIS sea 0 y sin ningun SIS mayor a 0.
            $resumen = DB::table('form_det as fd')
                ->leftJoin('almacenes as a', 'fd.CODIGO_PRE', '=', 'a.cod_ipress')
                ->selectRaw('
                    fd.CODIGO_PRE,
                    MAX(a.nombre_ipress) as nombre_ipress,
                    SUM(COALESCE(fd.SIS, 0)) as total_sis,
                    COUNT(*) as total_productos
                ')
                ->where('fd.ANNOMES', $annomes)
                ->groupBy('fd.CODIGO_PRE')
                ->havingRaw('SUM(COALESCE(fd.SIS, 0)) = 0')
                ->havingRaw('MAX(COALESCE(fd.SIS, 0)) = 0')
                ->orderByDesc('total_productos')
                ->get();

            // Detalle por producto para las filas expandibles
            $detalle = DB::table('form_det as fd')
                ->leftJoin('productos as p', 'fd.CODIGO_MED', '=', 'p.cod_sismed')
                ->select(
                    'fd.CODIGO_PRE',
                    'fd.CODIGO_MED',
                    'p.descripcion_sismed',
                    'fd.SIS',
                    'fd.SALDO',
                    'fd.STOCK_FIN'
                )
                ->where('fd.ANNOMES', $annomes)
                ->where(function ($q) {
                    $q->where('fd.SIS', 0)->orWhereNull('fd.SIS');
                })
                ->orderBy('fd.CODIGO_PRE')
                ->orderBy('fd.CODIGO_MED')
                ->get()
                ->groupBy('CODIGO_PRE');
        }

        return view('inconsistencias.index', compact(
            'mesesDisponibles',
            'annomes',
            'resumen',
            'detalle'
        ));
    }
}
