<?php

namespace App\Http\Controllers;

use App\Models\DjangoConfig;
use App\Models\Requerimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class PedidoController extends Controller
{
    public function index(Request $request)
    {
        // Captura los valores enviados por GET (desde el formulario)
        $mes = $request->query('mes');
        $anio = $request->query('anio');

        // Obtener los meses disponibles (donde hay registros con fecha_registro)
        $mesesDisponibles = Requerimiento::whereNotNull('fecha_registro')
            ->select(DB::raw('DISTINCT MONTH(fecha_registro) as mes'))
            ->pluck('mes')
            ->sort()
            ->values();

        // Obtener los años disponibles
        $aniosDisponibles = Requerimiento::whereNotNull('fecha_registro')
            ->select(DB::raw('DISTINCT YEAR(fecha_registro) as anio'))
            ->pluck('anio')
            ->sort()
            ->reverse()
            ->values();

        // Si el usuario seleccionó ambos filtros (mes y año)
        if ($mes && $anio) {

            // Construimos la consulta principal
            $query = Requerimiento::select(
                'almacen_id',
                'fecha_registro',
                DB::raw('MIN(id) as pedido_id'),
                DB::raw('COUNT(*) as productos_count')
            )
            ->with('almacen')
            ->groupBy('almacen_id', 'fecha_registro')
            ->orderBy('fecha_registro', 'desc')
            ->whereMonth('fecha_registro', $mes)
            ->whereYear('fecha_registro', $anio);

            // Si el usuario NO es administrador, filtramos por su ID
            if (!Auth::user()->is_admin) {
                $query->where('user_id', Auth::id());
            }

            // Ejecutamos la consulta solo si hay filtros
            $pedidos = $query->get();

        } else {
            // Si no hay filtros, devolvemos una colección vacía
            $pedidos = collect();
        }

        // Retornamos la vista con los datos
        return view('pedidos.index', compact(
            'pedidos',
            'mes',
            'anio',
            'mesesDisponibles',
            'aniosDisponibles'
        ));
    }


    public function getProductos(Request $request)
    {
        $pedidoId = $request->query('pedido_id');

        // Obtener el primer registro del grupo para sacar almacen_id y fecha_registro
        $referencia = Requerimiento::find($pedidoId);
        if (!$referencia) {
            return response()->json([]);
        }

        $productos = Requerimiento::with('producto')
            ->where('almacen_id', $referencia->almacen_id)
            ->where('fecha_registro', $referencia->fecha_registro)
            ->get()
            ->map(function ($req) {
                return [
                    'cod_sismed' => $req->cod_sismed,
                    'descripcion_producto' => $req->producto ? $req->producto->descripcion_producto : $req->descripcion_producto,
                    'req_final' => $req->req_final,
                ];
            });

        return response()->json($productos);
    }

    public function generarFER($pedidoId)
    {
        // Obtener el pedido base (primer registro del grupo)
        $referencia = Requerimiento::find($pedidoId);
        if (!$referencia) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        // Obtener todos los productos del mismo grupo (mismo almacen_id y fecha_registro)
        $productos = Requerimiento::with('producto')
            ->where('almacen_id', $referencia->almacen_id)
            ->where('fecha_registro', $referencia->fecha_registro)
            ->get();

        // Datos del almacen
        $almacen = $referencia->almacen;

        // Formatear fecha
        $fechaFormateada = \Carbon\Carbon::parse($referencia->fecha_registro)->format('d/m/Y');

        // Devolver vista renderizada como HTML (para cargar en el modal)
        return view('pedidos.fer', compact('almacen', 'productos', 'fechaFormateada'));
    }


}