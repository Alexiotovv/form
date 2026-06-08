<?php

namespace App\Http\Controllers;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InconsistenciasController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $annomes = $request->input('annomes');

        if (! $annomes) {
            return back()->with('error', 'Debes seleccionar un periodo para exportar.');
        }

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

        $fileName = 'inconsistencias_sis_' . $annomes . '_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($resumen) {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToFile('php://output');

            $writer->addRow(WriterEntityFactory::createRowFromArray([
                'CODIGO_PRE',
                'NOMBRE_IPRESS',
                'TOTAL_SIS',
                'TOTAL_PRODUCTOS',
            ]));

            foreach ($resumen as $fila) {
                $writer->addRow(WriterEntityFactory::createRowFromArray([
                    $fila->CODIGO_PRE,
                    $fila->nombre_ipress ?? 'SIN NOMBRE',
                    (float) $fila->total_sis,
                    (int) $fila->total_productos,
                ]));
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

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
