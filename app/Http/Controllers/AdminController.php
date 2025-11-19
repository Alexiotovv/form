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

    public function dashboard()
    {
        // === Gráfico de avance SIGA ===
        $totalAlmacenes = \App\Models\Almacen::where('para_descarga_siga', 'SI')->count();

        $enviaronEsteMes = \App\Models\Registro::whereMonth('fecha_envio', now()->month)
            ->whereYear('fecha_envio', now()->year)
            ->distinct('almacen_id')
            ->count('almacen_id');

        $porcentaje = $totalAlmacenes > 0 ? round(($enviaronEsteMes / $totalAlmacenes) * 100, 2) : 0;

        // === Gráfico de requerimientos por almacén ===
        $requerimientos = \App\Models\Requerimiento::with('almacen')
            ->whereMonth('fecha_registro', now()->month)
            ->whereYear('fecha_registro', now()->year)
            ->selectRaw('almacen_id, SUM(req_final) as total_requerimiento')
            ->groupBy('almacen_id')
            ->get();

        // Armamos etiquetas (cod_ipress + nombre_ipress)
        $labels = [];
        $data = [];
        foreach ($requerimientos as $r) {
            if ($r->almacen) {
                $labels[] = $r->almacen->cod_ipress . ' - ' . $r->almacen->nombre_ipress;
                $data[] = $r->total_requerimiento;
            }
        }

        return view('admin.dashboard', compact(
            'totalAlmacenes',
            'enviaronEsteMes',
            'porcentaje',
            'labels',
            'data'
        ));
    }



}

