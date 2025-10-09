<?php

namespace App\Http\Controllers;

use App\Models\ProcesamientoHistorico;
use Illuminate\Http\Request;

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
}
