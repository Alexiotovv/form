<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Establecimiento;
use App\Models\Registro;
use App\Models\Profesion;
use App\Models\Plazo;
use Illuminate\Support\Facades\Storage;

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

        $rutaArchivo = $request->file('archivo')->store('archivos', 'public');

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

        return redirect()->back()->with('success', '¡Registro enviado correctamente!');
    
    }
}
