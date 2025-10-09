<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Models\Establecimiento;
use App\Models\Almacen;
use App\Models\Registro;
use App\Models\Profesion;
use App\Models\Plazo;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Log;


class RegistroController extends Controller
{

    public function registrosici(Request $request)
    {
        
        // Validar que las fechas sean correctas
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_final' => 'required|date|after_or_equal:fecha_inicio',
        ]);
        $usuario = $request->user();

        // Si deseas filtrar por el usuario logueado, añade alguna relación en Registro, como user_id
        // Suponiendo que no hay user_id, y solo quieres devolver todos los registros con ese filtro:
        $registros = Registro::select('fecha_envio', 'hora_envio', 'archivo')
        ->whereBetween('fecha_envio', [
            $request->fecha_inicio,
            $request->fecha_final
        ])
        ->orderBy('fecha_envio', 'desc')
        ->get();
        
        $registros->transform(function ($registro) {
            $registro->archivo_url = url('storage/' . $registro->archivo);
            return $registro;
        });

        

        return response()->json([ 'data' => $registros]);
    }

    public function create()
    {
        $almacenes = Almacen::all();
        $profesiones = Profesion::all();
        $plazo   = Plazo::first();          // única fila
        $inicio  = $plazo?->dia_inicio ?? 1;
        $fin     = $plazo?->dia_fin    ?? 5;
        $hoy     = now()->day;
        $dentroDelPlazo = $hoy >= $inicio && $hoy <= $fin;
        $almacenUsuarioId = auth()->user()->almacen_id;

        return view('registro.create', compact(
            'almacenes', 
            'profesiones',
            'dentroDelPlazo',
            'inicio',
            'fin',
            'almacenUsuarioId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'correo' => 'required|email',
            'telefono' => 'required|string|max:20',
            'profesion_id' => 'required|exists:profesiones,id',
            // 'establecimiento_id' => 'required|exists:establecimientos,id',
            'archivo' => 'required|file|mimes:zip|max:50000', // máximo 50MB
            'terminos' => 'accepted',
        ]);
        // 🔒 Obtener el establecimiento del USUARIO AUTENTICADO (no del request)
        $usuario = auth()->user();
        $almacenId = $usuario->almacen_id;
        // dd($establecimientoId);
        // Verificar que el usuario tenga un establecimiento asignado
        if (!$almacenId) {
            return redirect()->back()->with('error', 'Tu cuenta no tiene un establecimiento asignado. Contacta al administrador.');
        }

        $almacenId = Almacen::findOrFail($almacenId);

        // 2. Contar cuántos envíos ha hecho este mes
        $enviosActuales = Registro::where('almacen_id', $almacenId->id)
            ->whereMonth('fecha_envio', now()->month)
            ->whereYear('fecha_envio', now()->year)
            ->count();

        // 3. Validar contra el límite mensual
            if ($enviosActuales >= $almacenId->envios) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "El establecimiento '{$almacenId->nombre_ipress}' no puede realizar más de {$almacenId->envios} envíos este mes.");
            }


        $codigo = $almacen->cod_ipress ?? ''; // puede ser null
        $fechaHora = now()->format('Ymd_His');
        $prefijo = $codigo ? $codigo . '' : ''; // si hay código, añade "_"
        $nombreArchivo = 'F'.'-'.$prefijo .'-'.'F01'. $fechaHora . '.' . $request->file('archivo')->getClientOriginalExtension();

        $rutaArchivo = $request->file('archivo')->storeAs('archivos', $nombreArchivo, 'public');
        
        // dd($establecimiento->id);
        Registro::create([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'telefono' => $request->telefono,
            'profesion_id' => $request->profesion_id,
            'almacen_id' => $almacenId->id,
            'fecha_envio' => now()->toDateString(),
            'hora_envio' => now()->toTimeString(),
            'archivo' => $rutaArchivo,
            'user_id' =>  auth()->user()->id,
        ]);
        // $establecimiento = Establecimiento::find($establecimientoId);
        return redirect()->route('gracias')->with([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'fecha' => now()->toDateString(),
            'hora' => now()->toTimeString(),
            'ruta_descarga'=>asset(Storage::url($rutaArchivo)),
            'almacen' => $almacenId->nombre_ipress
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
