<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class EstablecimientoController extends Controller
{
    public function index()
    {
        try {
            $establecimientos = Establecimiento::all();
            return view('establecimientos.index', compact('establecimientos'));
            
        } catch (Exception $e) {
            Log::error('Error al listar establecimientos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al cargar los establecimientos');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:establecimientos,nombre',
                'codigo' => 'string|max:255',
            ]);

            Establecimiento::create($validated);

            return redirect()->back()
                ->with('success', 'Establecimiento creado con éxito');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
                
        } catch (Exception $e) {
            Log::error('Error al crear establecimiento: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al crear el establecimiento')
                ->withInput();
        }
    }

    public function update(Request $request, Establecimiento $establecimiento)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:establecimientos,nombre,'.$establecimiento->id,
                'codigo' => 'string|max:255',
            ]);

            $establecimiento->update($validated);

            return redirect()->back()
                ->with('success', 'Establecimiento actualizado correctamente');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
                
        } catch (Exception $e) {
            Log::error('Error al actualizar establecimiento ID '.$establecimiento->id.': '.$e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al actualizar el establecimiento')
                ->withInput();
        }
    }

    public function destroy(Establecimiento $establecimiento)
    {
        try {
            $nombreEstablecimiento = $establecimiento->nombre;
            $establecimiento->delete();

            return redirect()->back()
                ->with('success', "Establecimiento '$nombreEstablecimiento' eliminado correctamente");
                
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Error de integridad al eliminar establecimiento: '.$e->getMessage());
            return redirect()->back()
                ->with('error', 'No se puede eliminar el establecimiento porque tiene registros asociados');
                
        } catch (Exception $e) {
            Log::error('Error al eliminar establecimiento ID '.$establecimiento->id.': '.$e->getMessage());
            return redirect()->back()
                ->with('error', 'Error inesperado al eliminar el establecimiento');
        }
    }
}