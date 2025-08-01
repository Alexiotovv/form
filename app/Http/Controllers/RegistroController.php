<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Establecimiento;
use App\Models\Registro;
use App\Models\Profesion;
use App\Models\Plazo;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Log;


class RegistroController extends Controller
{
    public function create()
    {
        $establecimientos = Establecimiento::all();
        $profesiones = Profesion::all();
        $plazo   = Plazo::first();          // única fila
        $inicio  = $plazo?->dia_inicio ?? 1;
        $fin     = $plazo?->dia_fin    ?? 5;
        $hoy     = now()->day;
        $dentroDelPlazo = $hoy >= $inicio && $hoy <= $fin;
        return view('registro.create', compact(
            'establecimientos', 
            'profesiones',
            'dentroDelPlazo',
            'inicio',
            'fin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'correo' => 'required|email',
            'telefono' => 'required|string|max:20',
            'profesion_id' => 'required|exists:profesiones,id',
            'establecimiento_id' => 'required|exists:establecimientos,id',
            'archivo' => 'required|file|mimes:zip|max:10240', // máximo 10MB
            'terminos' => 'accepted',
        ]);

        $establecimiento = Establecimiento::find($request->establecimiento_id);
        $codigo = $establecimiento->codigo ?? ''; // puede ser null
        $fechaHora = now()->format('Ymd_His');
        $prefijo = $codigo ? $codigo . '_' : ''; // si hay código, añade "_"
        $nombreArchivo = 'F'.'-'.$prefijo .'-'.'F01'. $fechaHora . '.' . $request->file('archivo')->getClientOriginalExtension();

        $rutaArchivo = $request->file('archivo')->storeAs('archivos', $nombreArchivo, 'public');

        Registro::create([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'telefono' => $request->telefono,
            'profesion_id' => $request->profesion_id,
            'establecimiento_id' => $request->establecimiento_id,
            'fecha_envio' => now()->toDateString(),
            'hora_envio' => now()->toTimeString(),
            'archivo' => $rutaArchivo,
        ]);
        $establecimiento = Establecimiento::find($request->establecimiento_id);
        return redirect()->route('gracias')->with([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'fecha' => now()->toDateString(),
            'hora' => now()->toTimeString(),
            'ruta_descarga'=>asset(Storage::url($rutaArchivo)),
            'establecimiento' => $establecimiento->nombre
        ]);

    }

    public function destroy(Registro $registro)
    {
        try {
            // 1. Guardar datos para el mensaje antes de eliminar
            $nombreCompleto = $registro->nombres . ' ' . $registro->apellidos;
            $rutaArchivo = $registro->archivo;
            
            // 2. Eliminar el archivo físico si existe
            if ($rutaArchivo && Storage::disk('public')->exists($rutaArchivo)) {
                Storage::disk('public')->delete($rutaArchivo);
            }
            
            // 3. Eliminar el registro de la base de datos
            $registro->delete();
            
            return redirect()->back()
                ->with('success', "Registro de $nombreCompleto eliminado correctamente");
                
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Error de integridad al eliminar registro: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'No se pudo eliminar el registro porque tiene relaciones dependientes');
                
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
            Log::error('Archivo no encontrado al eliminar registro ID ' . $registro->id . ': ' . $e->getMessage());
            $registro->delete(); // Eliminar el registro aunque no esté el archivo
            
            return redirect()->back()
                ->with('warning', 'Registro eliminado pero no se encontró el archivo asociado');
                
        } catch (Exception $e) {
            Log::error('Error al eliminar registro ID ' . $registro->id . ': ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Ocurrió un error inesperado al eliminar el registro');
        }
    }

}
