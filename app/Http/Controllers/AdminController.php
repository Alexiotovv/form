<?php

namespace App\Http\Controllers;

use App\Models\Registro;

class AdminController extends Controller
{
    public function index()
    {
        
            $user = auth()->user();

            $registros = Registro::with('almacen', 'profesion')
                ->when(!$user->is_admin, function ($query) use ($user) {
                    return $query->where('user_id', $user->id);
                })
                ->when(request('search'), function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('correo', 'like', "%{$search}%")
                        ->orWhere('fecha_envio', 'like', "%{$search}%") // 👈 búsqueda por fecha
                        ->orWhereHas('profesion', fn($p) => $p->where('nombre_profesion', 'like', "%{$search}%"))
                        ->orWhereHas('almacen', fn($e) => $e->where('nombre_ipress', 'like', "%{$search}%"));
                    });
                })
                ->latest()
                ->paginate(20)   // 👈 activa paginación
                ->withQueryString(); // 👈 mantiene el ?search= en la URL al cambiar de página

                return view('registro.index', compact('registros'));



    }

    public function dashboard(){
        return view('admin.dashboard');
    }


}

