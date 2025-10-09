<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use ZipArchive;
use Illuminate\Support\Carbon;

class ArchivoController extends Controller
{
    public function index(Request $request)
    {
        
        // Obtener años y meses disponibles desde la tabla registros
        $anios = Registro::selectRaw('YEAR(fecha_envio) as anio')->distinct()->pluck('anio');
        $meses = Registro::selectRaw('MONTH(fecha_envio) as mes')->distinct()->pluck('mes');
        
        $archivos = collect();
        
        if ($request->filled(['anio', 'mes'])) {
            $archivos = Registro::whereYear('fecha_envio', $request->anio)
                ->whereMonth('fecha_envio', $request->mes)
                ->get();
        }
        
        return view('archivos.index', compact('anios', 'meses', 'archivos'));
    }

    
    public function descargar($id)
    {
        $registro = Registro::findOrFail($id);

        // El campo ya contiene la ruta completa "archivos/archivo.zip"
        $path = $registro->archivo;

        if (!Storage::disk('public')->exists($path)) {
            return back()->with('error', 'El archivo no existe en el servidor.');
        }

        // basename() para que el archivo descargado no incluya "archivos/"
        return Storage::disk('public')->download($path, basename($registro->archivo));
    }

    //Descarga todos los archivos juntos
    public function descargarZip(Request $request)
    {
        $anio = $request->input('anio');
        $mes = $request->input('mes');

        if (!$anio || !$mes) {
            return back()->with('error', 'Debe seleccionar año y mes.');
        }

        // Buscar archivos en registros
        $archivos = Registro::whereYear('fecha_envio', $anio)
            ->whereMonth('fecha_envio', $mes)
            ->get();

        if ($archivos->isEmpty()) {
            return back()->with('error', 'No hay archivos para el año y mes seleccionados.');
        }
        // Nombre dinámico: ici_2025_08_121045123.zip
        $now = Carbon::now();
        $nombreZip = 'ici_' . $anio . '_' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '_' .
            $now->format('Hisv') . '.zip';

        $zipPath = storage_path('app/public/tmp/' . $nombreZip);

        // Crear carpeta temporal si no existe
        if (!file_exists(storage_path('app/public/tmp'))) {
            mkdir(storage_path('app/public/tmp'), 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($archivos as $registro) {
                $path = storage_path('app/public/' . $registro->archivo);
                if (file_exists($path)) {
                    $zip->addFile($path, basename($registro->archivo));
                }
            }
            $zip->close();
        } else {
            return back()->with('error', 'No se pudo crear el archivo ZIP.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

}
