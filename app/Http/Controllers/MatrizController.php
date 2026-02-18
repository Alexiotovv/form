<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\Producto;
use App\Models\User;
// use App\Models\FormDet;
use Illuminate\Http\Request;
// use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Support\Facades\DB;


class MatrizController extends Controller
{

    public function index(Request $request)
    {
        $codigo_pre = $request->input('cod_ipress');
        $codigo_sismed = $request->input('cod_sismed');
        $fechaManual = $request->input('fin_mes') ?? date('Y-m-t');

        // Validar fecha
        if (!strtotime($fechaManual)) {
            $fechaManual = date('Y-m-t');
        }

        if (empty($codigo_pre)) {
            $registros = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                50,
                1
            );
            return view('matriz.index', compact('registros'));
        }

        // ✅ Llamamos a la función reutilizable
        $query = $this->obtenerRegistrosMatriz($codigo_pre, $codigo_sismed, $fechaManual);
        
        // ✅ Paginamos
        $registros = $query->paginate(50);
        

        return view('matriz.index', compact('registros'));
    }

    private function obtenerRegistrosMatriz($codigo_pre, $codigo_sismed, $fechaManual)
    {
        $camposConsumo = 'form_det_filtrado.CREDHOSP + form_det_filtrado.DEFNAC + form_det_filtrado.EXO + form_det_filtrado.INTERSAN + form_det_filtrado.OTR_CONV + form_det_filtrado.SIS + form_det_filtrado.SOAT + form_det_filtrado.VENTA + form_det_filtrado.OTRAS_SAL';

        // 🧱 Construimos la subconsulta dinámicamente
        $subQuery = "
            SELECT
                CODIGO_PRE,
                CODIGO_MED,
                FECHA,
                CREDHOSP,
                DEFNAC,
                EXO,
                INTERSAN,
                OTR_CONV,
                SIS,
                SOAT,
                VENTA,
                OTRAS_SAL,
                STOCK_FIN,
                PRECIO,
                ROW_NUMBER() OVER (
                    PARTITION BY CODIGO_PRE, CODIGO_MED
                    ORDER BY STOCK_FIN ASC
                ) as rn_stock,
                INGRE,
                FEC_EXP
            FROM form_det
            WHERE FECHA >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            AND CODIGO_PRE = ?";

        $bindings = [$codigo_pre];

        // Si viene cod_sismed, lo agregamos al filtro
        if (!empty($codigo_sismed)) {
            $subQuery .= " AND CODIGO_MED = ?";
            $bindings[] = $codigo_sismed;
        }

        $formDetFiltrado = DB::raw("({$subQuery}) AS form_det_filtrado");

        // 🧱 Paso 1: Construimos la consulta base
        $baseQuery = Almacen::query()
            ->join($formDetFiltrado, 'almacenes.cod_ipress', '=', 'form_det_filtrado.CODIGO_PRE')
            ->join('productos', 'productos.cod_sismed', '=', 'form_det_filtrado.CODIGO_MED')
            ->where('productos.estado', 'C')
            ->select(
                'almacenes.id',
                'productos.id as producto_id',
                DB::raw('MAX(almacenes.disa_diresa) as disa_diresa'),
                DB::raw('MAX(almacenes.ue_mef) as ue_mef'),
                DB::raw('MAX(almacenes.almacen_pertenece) as almacen_pertenece'),
                DB::raw('MAX(almacenes.red) as red'),
                DB::raw('MAX(almacenes.microred) as microred'),
                DB::raw('MAX(almacenes.distrito) as distrito'),
                'almacenes.cod_ipress',
                DB::raw('MAX(almacenes.nombre_ipress) as nombre_ipress'),
                DB::raw('MAX(almacenes.tipo_establecimiento) as tipo_establecimiento'),
                DB::raw('MAX(almacenes.ipress_dengue) as ipress_dengue'),
                DB::raw('MAX(almacenes.nivel) as nivel'),
                DB::raw('MAX(almacenes.universo_ipress) as universo_ipress'),

                'productos.cod_sismed',
                DB::raw('MAX(productos.cod_unificado) as cod_unificado'),
                DB::raw('MAX(productos.tipo_prod) as tipo_prod'),
                DB::raw('MAX(productos.tipo_abastecimiento) as tipo_abastecimiento'),
                DB::raw('MAX(productos.peti2023) as peti2023'),
                DB::raw('MAX(productos.estado) as estado'),
                DB::raw('MAX(productos.producto_fed_actual) as producto_fed_actual'),
                DB::raw('MAX(productos.producto_cap_eca) as producto_cap_eca'),
                DB::raw('MAX(productos.iras) as iras'),
                DB::raw('MAX(productos.dengue) as dengue'),
                DB::raw('MAX(productos.dengue_grupo_a) as dengue_grupo_a'),
                DB::raw('MAX(productos.dengue_grupo_b) as dengue_grupo_b'),
                DB::raw('MAX(productos.dengue_grupo_c) as dengue_grupo_c'),
                DB::raw('MAX(productos.lista_1) as lista_1'),
                DB::raw('MAX(productos.lista_2) as lista_2'),

                DB::raw('MAX(productos.descripcion_cubo) as descripcion_cubo'),
                DB::raw('MAX(productos.descripcion_producto) as descripcion_producto'),
                DB::raw('MAX(productos.descripcion_producto_alt) as descripcion_producto_alt'),

                DB::raw("SUM(CASE WHEN DATE_FORMAT(form_det_filtrado.FECHA,'%Y%m') = DATE_FORMAT(CURDATE() - INTERVAL 11 MONTH,'%Y%m') THEN ({$camposConsumo}) ELSE 0 END) as Mes1"),
                DB::raw("SUM(CASE WHEN DATE_FORMAT(form_det_filtrado.FECHA,'%Y%m') = DATE_FORMAT(CURDATE() - INTERVAL 10 MONTH,'%Y%m') THEN ({$camposConsumo}) ELSE 0 END) as Mes2"),
                DB::raw("SUM(CASE WHEN DATE_FORMAT(form_det_filtrado.FECHA,'%Y%m') = DATE_FORMAT(CURDATE() - INTERVAL 9 MONTH,'%Y%m') THEN ({$camposConsumo}) ELSE 0 END) as Mes3"),
                DB::raw("SUM(CASE WHEN DATE_FORMAT(form_det_filtrado.FECHA,'%Y%m') = DATE_FORMAT(CURDATE() - INTERVAL 8 MONTH,'%Y%m') THEN ({$camposConsumo}) ELSE 0 END) as Mes4"),
                DB::raw("SUM(CASE WHEN DATE_FORMAT(form_det_filtrado.FECHA,'%Y%m') = DATE_FORMAT(CURDATE() - INTERVAL 7 MONTH,'%Y%m') THEN ({$camposConsumo}) ELSE 0 END) as Mes5"),
                DB::raw("SUM(CASE WHEN DATE_FORMAT(form_det_filtrado.FECHA,'%Y%m') = DATE_FORMAT(CURDATE() - INTERVAL 6 MONTH,'%Y%m') THEN ({$camposConsumo}) ELSE 0 END) as Mes6"),
                DB::raw("SUM(CASE WHEN DATE_FORMAT(form_det_filtrado.FECHA,'%Y%m') = DATE_FORMAT(CURDATE() - INTERVAL 5 MONTH,'%Y%m') THEN ({$camposConsumo}) ELSE 0 END) as Mes7"),
                DB::raw("SUM(CASE WHEN DATE_FORMAT(form_det_filtrado.FECHA,'%Y%m') = DATE_FORMAT(CURDATE() - INTERVAL 4 MONTH,'%Y%m') THEN ({$camposConsumo}) ELSE 0 END) as Mes8"),
                DB::raw("SUM(CASE WHEN DATE_FORMAT(form_det_filtrado.FECHA,'%Y%m') = DATE_FORMAT(CURDATE() - INTERVAL 3 MONTH,'%Y%m') THEN ({$camposConsumo}) ELSE 0 END) as Mes9"),
                DB::raw("SUM(CASE WHEN DATE_FORMAT(form_det_filtrado.FECHA,'%Y%m') = DATE_FORMAT(CURDATE() - INTERVAL 2 MONTH,'%Y%m') THEN ({$camposConsumo}) ELSE 0 END) as Mes10"),
                DB::raw("SUM(CASE WHEN DATE_FORMAT(form_det_filtrado.FECHA,'%Y%m') = DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH,'%Y%m') THEN ({$camposConsumo}) ELSE 0 END) as Mes11"),
                DB::raw("SUM(CASE WHEN DATE_FORMAT(form_det_filtrado.FECHA,'%Y%m') = DATE_FORMAT(CURDATE(), '%Y%m') THEN ({$camposConsumo}) ELSE 0 END) as Mes12"),

                DB::raw('MAX(CASE WHEN form_det_filtrado.rn_stock = 1 THEN form_det_filtrado.STOCK_FIN END) as StockFinal'),
                DB::raw('MAX(form_det_filtrado.INGRE) as ingre'),
                DB::raw('MAX(form_det_filtrado.FEC_EXP) as fec_exp'),

                DB::raw("SUM({$camposConsumo}) as consumo_total"),
                //SOLAMENTE DIVIDIR ENTRE LAS CANTIDADES DE LOS MESES QUE HUBO CONSUNO
                //SI HUBO 10 MESES CONSUMO /10
                DB::raw("
                    CASE
                        WHEN SUM(
                            CASE 
                                WHEN ({$camposConsumo}) > 0 THEN 1 
                                ELSE 0 
                            END
                        ) > 0
                        THEN FLOOR(
                            SUM({$camposConsumo}) / 
                            SUM(
                                CASE 
                                    WHEN ({$camposConsumo}) > 0 THEN 1 
                                    ELSE 0 
                                END
                            )
                        )
                        ELSE 0
                    END as cpma
                "),


                DB::raw("SUM(
                    CASE
                        WHEN DATE_FORMAT(form_det_filtrado.FECHA, '%Y%m') BETWEEN DATE_FORMAT(CURDATE() - INTERVAL 3 MONTH, '%Y%m') AND DATE_FORMAT(CURDATE(), '%Y%m')
                        THEN ({$camposConsumo}) ELSE 0
                    END
                ) as consumo_ultimos_4meses"),

                DB::raw("CASE
                    WHEN SUM({$camposConsumo}) > 0
                    THEN ROUND(
                        MAX(CASE WHEN form_det_filtrado.rn_stock = 1 THEN form_det_filtrado.STOCK_FIN END) / (SUM({$camposConsumo}) / 12.0),
                        2
                    )
                    ELSE 0
                END as meses_prov"),

                DB::raw('CASE
                    WHEN MAX(CASE WHEN form_det_filtrado.rn_stock = 1 THEN form_det_filtrado.STOCK_FIN END) IS NOT NULL
                    THEN ROUND(
                        MAX(CASE WHEN form_det_filtrado.rn_stock = 1 THEN form_det_filtrado.STOCK_FIN END) *
                        MAX(CASE WHEN form_det_filtrado.rn_stock = 1 THEN form_det_filtrado.PRECIO END),
                        2
                    )
                    ELSE 0
                END as monto'),

                DB::raw("
                    CASE
                        WHEN MAX(CASE WHEN form_det_filtrado.rn_stock = 1 THEN form_det_filtrado.STOCK_FIN END) > 0
                        THEN TIMESTAMPDIFF(
                            MONTH,
                            DATE('{$fechaManual}'),
                            MAX(CASE WHEN form_det_filtrado.rn_stock = 1 THEN form_det_filtrado.FEC_EXP END)
                        )
                        ELSE NULL
                    END as meses_para_vencimiento
                "),

                DB::raw("
                    CASE
                        WHEN MAX(CASE WHEN form_det_filtrado.rn_stock = 1 THEN form_det_filtrado.STOCK_FIN END) > 0 THEN
                            CASE
                                WHEN TIMESTAMPDIFF(
                                    MONTH,
                                    DATE('{$fechaManual}'),
                                    MAX(CASE WHEN form_det_filtrado.rn_stock = 1 THEN form_det_filtrado.FEC_EXP END)
                                ) <= 0 THEN 'VENCIDO'
                                WHEN TIMESTAMPDIFF(
                                    MONTH,
                                    DATE('{$fechaManual}'),
                                    MAX(CASE WHEN form_det_filtrado.rn_stock = 1 THEN form_det_filtrado.FEC_EXP END)
                                ) > 6 THEN ''
                                ELSE 'POR VENCER'
                            END
                        ELSE ''
                    END as sit_fecha_vcmto
                ")
            )
            ->groupBy(
                'almacenes.id',
                'almacenes.cod_ipress',
                'productos.cod_sismed',
                'productos.id');

        // Asignamos los bindings a la subconsulta
        foreach ($bindings as $binding) {
            $baseQuery->addBinding($binding, 'join');
        }

        // Paso 2: Envuelve la base y agrega situacion_stock
        $wrappedQuery = DB::table(DB::raw("({$baseQuery->toSql()}) as base"))
            ->mergeBindings($baseQuery->getQuery())
            ->select('*', DB::raw("
                CASE
                    WHEN StockFinal < 0 THEN 'SALDO NEGATIVO'
                    WHEN lista_1 = 'Gran Volumen' AND meses_prov BETWEEN 1 AND 6 THEN 'NORMOSTOCK'
                    WHEN StockFinal > 0 AND cpma > 0 AND meses_prov > 6 THEN 'SOBRESTOCK'
                    WHEN StockFinal > 0 AND cpma = 0 AND meses_prov = 0 THEN 'SIN ROTACION'
                    WHEN StockFinal > 0 AND cpma > 0 AND meses_prov BETWEEN 2 AND 6 THEN 'NORMOSTOCK'
                    WHEN StockFinal > 0 AND cpma > 0 AND meses_prov > 0 AND meses_prov < 2 THEN 'SUBSTOCK'
                    WHEN StockFinal = 0 AND cpma > 0 AND meses_prov = 0 AND consumo_ultimos_4meses > 0 THEN 'DESABASTECIDO'
                    WHEN StockFinal = 0 AND cpma > 0 AND meses_prov = 0 AND consumo_ultimos_4meses = 0 THEN 'SIN CONSUMO'
                    WHEN StockFinal = 0 AND cpma = 0 AND meses_prov = 0 AND consumo_ultimos_4meses = 0 THEN 'SIN DATOS'
                    ELSE 'SIN CLASIFICAR'
                END as situacion_stock
            "))
            ->orderBy('id', 'desc');

        return $wrappedQuery;
    }

    public function search(Request $request)
    {
        $q = $request->input('q');
        $tipo = $request->input('tipo');

        $user = auth()->user();
        $isAdmin = (bool) $user->is_admin;
        $results = [];

        if ($tipo === 'cod_ipress') {

            // Base query
            $query = Almacen::query();

            // 🔒 Si NO es admin, solo su almacén asignado
            if (!$isAdmin && $user->almacen_id) {
                $query->where('id', $user->almacen_id);
            } else {
                // Si es admin, puede buscar libremente
                $query->where(function ($sub) use ($q) {
                    $sub->where('cod_ipress', 'LIKE', "%{$q}%")
                        ->orWhere('nombre_ipress', 'LIKE', "%{$q}%");
                });
            }

            $results = $query->limit(20)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->cod_ipress,
                        'text' => $item->cod_ipress . ' - ' . $item->nombre_ipress,
                        'red' => $item->red,
                        'microred' => $item->microred,
                    ];
                });
        }

        elseif ($tipo === 'cod_sismed') {
            $results = Producto::where('cod_sismed', 'LIKE', "%{$q}%")
                ->orWhere('descripcion_producto', 'LIKE', "%{$q}%")
                ->orWhere('concentracion', 'LIKE', "%{$q}%")
                ->limit(20)
                ->get()
                ->map(function ($item) {
                    $text = $item->cod_sismed . ' - ' . $item->descripcion_producto;
                    if ($item->concentracion) {
                        $text .= ' (' . $item->concentracion . ')';
                    }
                    return [
                        'id' => $item->cod_sismed,
                        'text' => $text
                    ];
                });
        }

        return response()->json($results);
    }



    // 🔹 Función para mostrar la vista de requerimientos
    public function requerimientosIndex()
    {   $user = auth()->user();
        $almacen = \App\Models\Almacen::find($user->almacen_id);

        $cod_ipress = $almacen?->cod_ipress ?? '';
        $nombre_ipress = $almacen?->nombre_ipress ?? '';
        $red = $almacen?->red ?? '';
        $microred = $almacen?->microred ?? '';
        $isAdmin = (bool) $user->is_admin; // ✅ aquí usamos tu campo

        $ipress_list = Almacen::orderBy('nombre_ipress')->get();

        return view('requerimientos.index', compact('ipress_list','cod_ipress', 'nombre_ipress', 'red', 'microred', 'isAdmin'));
    }

    // 🔹 Función para obtener los datos de la tabla y resúmenes
    public function requerimientosData(Request $request)
    {
        $codigo_pre = $request->input('cod_ipress');
        if (!$codigo_pre) {
            return response()->json([
                'data' => [],
                'resumenes' => [
                    'desabastecido' => 0,
                    'substock' => 0,
                    'normostock' => 0,
                    'sobrestock' => 0,
                    'por_vencer' => 0,
                    'vencido' => 0
                ]
            ]);
        }

        // Usamos la misma función reutilizable
        $query = $this->obtenerRegistrosMatriz($codigo_pre, null, date('Y-m-t'));

        // Obtenemos los datos sin paginación (todos los registros)
        $registros = $query->get();

        // Preparar resúmenes
        $resumenes = [
            'desabastecido' => 0,
            'substock' => 0,
            'normostock' => 0,
            'sobrestock' => 0,
            'por_vencer' => 0,
            'vencido' => 0
        ];

        foreach ($registros as $item) {
            switch ($item->situacion_stock) {
                case 'DESABASTECIDO':
                    $resumenes['desabastecido']++;
                    break;
                case 'SUBSTOCK':
                    $resumenes['substock']++;
                    break;
                case 'NORMOSTOCK':
                    $resumenes['normostock']++;
                    break;
                case 'SOBRESTOCK':
                    $resumenes['sobrestock']++;
                    break;
            }

            if ($item->sit_fecha_vcmto === 'POR VENCER') {
                $resumenes['por_vencer']++;
            } elseif ($item->sit_fecha_vcmto === 'VENCIDO') {
                $resumenes['vencido']++;
            }
        }

        // Formatear datos para la tabla
        $data = $registros->map(function ($item) {
            return [
                'id' => $item->id, // Puedes usar un identificador único si lo necesitas
                'cod_sismed' => $item->cod_sismed,
                'descripcion_producto' => $item->descripcion_producto,
                'stock_final' => $item->StockFinal,
                'fecha_venc' => $item->fec_exp ? \Carbon\Carbon::parse($item->fec_exp)->format('d/m/Y') : null,
                'cpm' => $item->cpma,
                'msd' => $item->meses_prov, // Asumimos que MSD = meses_prov (si no, ajusta según fuente real)
                'situacion_stock' => $item->situacion_stock,
                'situacion_fecha_venc' => $item->sit_fecha_vcmto,
                'req_sugerido' => $item->consumo_total > 0 ? round($item->consumo_total / 12 * 3) : 0, // Ejemplo: 3 meses de consumo
                'req_final' => null // Por defecto vacío, se ingresa manualmente
            ];
        });

        return response()->json([
            'data' => $data,
            'resumenes' => $resumenes
        ]);
    }

    // 🔹 Función para guardar el REQ FINAL
    public function guardarRequerimiento(Request $request)
    {
        // try {

            $request->validate([
                'cod_sismed' => 'required|string',
                'cod_ipress' => 'required|string',
                'req_final' => 'nullable|integer|min:0'
            ]);

            $cod_sismed = $request->input('cod_sismed');
            $cod_ipress = $request->input('cod_ipress');
            $req_final = $request->input('req_final');

            $almacen = Almacen::where('cod_ipress', $cod_ipress)->first();
            if (!$almacen) {
                return response()->json(['success' => false, 'error' => 'IPRESS no encontrada']);
            }

            $producto = Producto::where('cod_sismed', $cod_sismed)->first();
            if (!$producto) {
                return response()->json(['success' => false, 'error' => 'Producto no encontrado']);
            }

            $datosMatriz = $this->obtenerRegistrosMatriz($cod_ipress, $cod_sismed, date('Y-m-t'))->first();

            $stock_final = $datosMatriz ? $datosMatriz->StockFinal : 0;
            $fecha_venc = $datosMatriz && $datosMatriz->fec_exp ? \Carbon\Carbon::parse($datosMatriz->fec_exp)->format('Y-m-d') : null;
            $cpm = $datosMatriz ? $datosMatriz->cpma : 0;
            $msd = $datosMatriz ? $datosMatriz->meses_prov : 0;
            $situacion_stock = $datosMatriz ? $datosMatriz->situacion_stock : 'SIN DATOS';
            $situacion_fecha_venc = $datosMatriz ? $datosMatriz->sit_fecha_vcmto : null;
            $req_sugerido = $datosMatriz ? round($datosMatriz->consumo_total / 12 * 3) : 0;

            $fecha_registro = \Carbon\Carbon::today()->toDateString();//date('Y-m-d'); // o: 
            $usuario = User::where('almacen_id', $almacen->id)->first();
            \App\Models\Requerimiento::updateOrCreate(
                [
                    'cod_sismed' => $cod_sismed,
                    'almacen_id' => $almacen->id,
                    'fecha_registro' => $fecha_registro,
                ],
                [
                    'descripcion_producto' => $producto->descripcion_producto,
                    'stock_final' => $stock_final,
                    'fecha_venc' => $fecha_venc,
                    'cpm' => $cpm,
                    'msd' => $msd,
                    'situacion_stock' => $situacion_stock,
                    'situacion_fecha_venc' => $situacion_fecha_venc,
                    'req_sugerido' => $req_sugerido,
                    'req_final' => $req_final,
                    'confirmado' => false, // siempre en false al guardar
                    'user_id' => optional($usuario)->id, // usuario actual
                ]
            );

            return response()->json(['success' => true]);
        // } catch (\Exception $e) {
        //     // Loguear el error real (solo en desarrollo o con logging adecuado)
        //     \Log::error('Error en guardarRequerimiento: ' . $e->getMessage());
            
        //     return response()->json([
        //         'success' => false,
        //         'error' => 'Error interno del servidor. Por favor, intente nuevamente.' 
        //     ], 500);
        // }
    }
    
    // 🔹 Obtener requerimientos NO confirmados del usuario actual
   public function getRequerimientosNoConfirmados()
    {
        $query = \App\Models\Requerimiento::with('almacen')
            ->where('confirmado', false);

        // 🔐 Si NO es admin, filtrar solo los del usuario
        if (!auth()->user()->is_admin) {
            $query->where('user_id', auth()->id());
        }

        $requerimientos = $query->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'cod_sismed' => $req->cod_sismed,
                    'descripcion_producto' => $req->descripcion_producto,
                    'stock_final' => $req->stock_final,
                    'req_final' => $req->req_final,
                    'ipress' => optional($req->almacen)->nombre_ipress ?? 'N/A',
                    'created_at' => $req->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json($requerimientos);
    }


    // 🔹 Confirmar todos los requerimientos del usuario
    public function confirmarRequerimientos(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            // Confirmar todos los no confirmados del usuario
            \App\Models\Requerimiento::where('user_id', auth()->id())
                ->where('confirmado', false)
                ->update(['confirmado' => true]);
        } else {
            // Confirmar solo los IDs especificados
            \App\Models\Requerimiento::where('user_id', auth()->id())
                ->whereIn('id', $ids)
                ->update(['confirmado' => true]);
        }

        return response()->json(['success' => true]);
    }

    // 🔹 Eliminar un requerimiento no confirmado
    public function eliminarRequerimiento($id)
    {
        $req = \App\Models\Requerimiento::where('id', $id)
            // ->where('user_id', auth()->id())
            ->where('confirmado', false)
            ->first();

        if ($req) {
            $req->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'error' => 'No encontrado o ya confirmado']);
    }


    // 🔹 Editar req_final de un requerimiento no confirmado
    public function editarRequerimiento(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:requerimientos,id',
            'req_final' => 'nullable|integer|min:0'
        ]);

        $requerimiento = \App\Models\Requerimiento::where('id', $request->id)
            ->where('user_id', auth()->id())
            ->where('confirmado', false)
            ->first();

        if (!$requerimiento) {
            return response()->json(['success' => false, 'error' => 'No encontrado o ya confirmado']);
        }

        $requerimiento->req_final = $request->req_final;
        $requerimiento->save();

        return response()->json(['success' => true]);
    }



}