<?php

namespace App\Http\Controllers;

use App\Jobs\ExportarMatrizJob;
use App\Models\ExportacionMatriz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MatrizExportacionController extends Controller
{
    public function index()
    {
        $exportaciones = ExportacionMatriz::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('matriz.exportacion', compact('exportaciones'));
    }
    
    public function exportar(Request $request)
    {
        $request->validate([
            'fin_mes' => 'required|date',
        ]);
        
        $filtros = [
            'cod_ipress' => $request->input('cod_ipress'),
            'cod_sismed' => $request->input('cod_sismed'),
            'fin_mes' => $request->input('fin_mes'),
            'tip_sum' => $request->input('tip_sum', []),
            'tipo_prod' => $request->input('tipo_prod', []),
            'tipo_abastecimiento' => $request->input('tipo_abastecimiento', []),
            'tipo_establecimiento' => $request->input('tipo_establecimiento', []),
            'peti2023' => $request->input('peti2023', []),
            'lista_1' => $request->input('lista_1', []),
        ];
        
        $exportacion = ExportacionMatriz::create([
            'user_id' => auth()->id(),
            'nombre_archivo' => '',
            'ruta_archivo' => '',
            'estado' => 'pendiente',
            'progreso' => 0,
            'filtros_aplicados' => $filtros,
            'mensaje' => 'Exportación encolada. Esperando procesamiento...'
        ]);
        
        // Dispatch job a la cola
        ExportarMatrizJob::dispatch($exportacion, $filtros);
        
        return response()->json([
            'success' => true,
            'exportacion_id' => $exportacion->id,
            'message' => 'Exportación iniciada. Recibirás una notificación cuando esté lista.'
        ]);
    }
    
    public function estado($id)
    {
        $exportacion = ExportacionMatriz::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();
            
        return response()->json([
            'estado' => $exportacion->estado,
            'progreso' => $exportacion->progreso,
            'mensaje' => $exportacion->mensaje,
            'nombre_archivo' => $exportacion->nombre_archivo,
            'total_registros' => $exportacion->total_registros,
            'registros_procesados' => $exportacion->registros_procesados,
        ]);
    }
    
    public function descargar($id)
    {
        $exportacion = ExportacionMatriz::where('user_id', auth()->id())
            ->where('id', $id)
            ->where('estado', 'completado')
            ->firstOrFail();
            
        if (!file_exists($exportacion->ruta_archivo)) {
            abort(404, 'Archivo no encontrado');
        }
        
        return response()->download($exportacion->ruta_archivo, $exportacion->nombre_archivo);
    }
    
    public function eliminar($id)
    {
        $exportacion = ExportacionMatriz::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();
            
        // Eliminar archivo físico
        if (file_exists($exportacion->ruta_archivo)) {
            unlink($exportacion->ruta_archivo);
        }
        
        $exportacion->delete();
        
        return response()->json(['success' => true]);
    }
}