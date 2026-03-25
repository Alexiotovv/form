<?php

namespace App\Http\Controllers;

use App\Models\ProcesamientoHistorico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProcesamientoHistoricoController extends Controller
{
    public function index(Request $request)
    {
        // Búsqueda simple
        $search = $request->input('search');

        $historicos = ProcesamientoHistorico::with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('fecha_ejecucion', 'like', "%{$search}%")
                    ->orWhere('tiempo_ejecucion', 'like', "%{$search}%")
                    ->orWhere('tablas_registros', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%"); // 👈 aquí busca por nombre de usuario
                    });
                });
            })
            ->latest()
            ->paginate(20)  
            ->withQueryString(); // mantiene ?search= al cambiar de página

        return view('historicos.index', compact('historicos', 'search'));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $procesamiento = ProcesamientoHistorico::findOrFail($id);
            
            // Los registros de form_det se eliminarán automáticamente 
            // gracias al onDelete('cascade') en la migración
            
            $procesamiento->delete();
            
            DB::commit();
            
            return redirect()->route('historicos.index')
                ->with('success', 'Procesamiento histórico y sus registros asociados eliminados correctamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('historicos.index')
                ->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}
