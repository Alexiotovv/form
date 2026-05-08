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
        
        // Capturar filtros adicionales
        $filtros = [
            'tip_sum' => $request->input('tip_sum', []),
            'tipo_prod' => $request->input('tipo_prod', []),
            'tipo_abastecimiento' => $request->input('tipo_abastecimiento', []),
            'tipo_establecimiento' => $request->input('tipo_establecimiento', []),
            'peti2023' => $request->input('peti2023', []),
            'lista_1' => $request->input('lista_1', []),
        ];
        
        if (empty($codigo_pre)) {
            $registros = collect();
            return view('matriz.index', compact('registros', 'filtros'));
        }
        
        // Obtener registros del SP
        $coleccion = $this->obtenerRegistrosMatriz($codigo_pre, $codigo_sismed, $fechaManual);
        
        // Función auxiliar para filtrar valores nulos
        $filtrarPorCampo = function($coleccion, $valoresFiltro, $campo) {
            if (empty($valoresFiltro)) {
                return $coleccion;
            }
            
            // Separar valores nulos de valores normales
            $incluirNulos = in_array('__NULL__', $valoresFiltro);
            $valoresNormales = array_filter($valoresFiltro, function($v) {
                return $v !== '__NULL__' && $v !== '';
            });
            
            return $coleccion->filter(function($item) use ($valoresNormales, $incluirNulos, $campo) {
                $valorCampo = $item->$campo;
                $esNulo = is_null($valorCampo) || $valorCampo === '' || $valorCampo === ' ';
                
                if ($incluirNulos && $esNulo) {
                    return true;
                }
                
                if (!empty($valoresNormales) && in_array($valorCampo, $valoresNormales)) {
                    return true;
                }
                
                return false;
            });
        };
        
        // Aplicar filtros adicionales
        $coleccion = $filtrarPorCampo($coleccion, $filtros['tip_sum'], 'TIPSUM');
        $coleccion = $filtrarPorCampo($coleccion, $filtros['tipo_prod'], 'tipo_prod');
        $coleccion = $filtrarPorCampo($coleccion, $filtros['tipo_abastecimiento'], 'tipo_abastecimiento');
        $coleccion = $filtrarPorCampo($coleccion, $filtros['tipo_establecimiento'], 'tipo_establecimiento');
        $coleccion = $filtrarPorCampo($coleccion, $filtros['peti2023'], 'peti2023');
        $coleccion = $filtrarPorCampo($coleccion, $filtros['lista_1'], 'lista_1');
        
        $coleccion = $this->procesarRegistrosProyectados($coleccion);
        $coleccion = $coleccion->sortBy('descripcion_producto_alt', SORT_NATURAL | SORT_FLAG_CASE);

        // Mostrar todos los registros
        $registros = $coleccion;
        return view('matriz.index', compact('registros', 'filtros'));
    }

    //CODIGO DESDE EL PROCEDIMIENTO ALMACENADO
    private function obtenerRegistrosMatriz($codigo_pre, $codigo_sismed, $fechaManual)
    {
        $fechaObj = \Carbon\Carbon::parse($fechaManual);
        $fechaReferencia = $fechaObj->format('Y-m-d');
        
        // 🧱 Llamada al procedimiento almacenado
        $results = DB::select("
            CALL sp_obtener_registros_matriz(?, ?, ?)
        ", [$codigo_pre, $codigo_sismed ?: null, $fechaReferencia]);
        

        
        // Retornar como Builder falso para mantener compatibilidad con paginate()
        // O mejor, retornar directamente la colección y ajustar el controller
        return collect($results);
        // return $collection; // Retorna colección, ajustar controller
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
        $fecha = $request->input('fecha', date('Y-m-t'));

        if (!strtotime($fecha)) {
            $fecha = date('Y-m-t');
        }

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

        // Usamos la misma función reutilizable - AHORA RETORNA COLECCIÓN
        $registros = $this->obtenerRegistrosMatriz($codigo_pre, null, date('Y-m-t'));

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
                'id' => $item->id ?? uniqid(), // Usar un identificador único si no hay ID
                'cod_sismed' => $item->cod_sismed,
                'descripcion_producto' => $item->descripcion_producto,
                'stock_final' => $item->StockFinal,
                'fecha_venc' => $item->fec_exp ? \Carbon\Carbon::parse($item->fec_exp)->format('d/m/Y') : null,
                'cpm' => $item->cpma,
                'msd' => $item->meses_prov,
                'situacion_stock' => $item->situacion_stock,
                'situacion_fecha_venc' => $item->sit_fecha_vcmto,
                'req_sugerido' => $item->consumo_total > 0 ? round($item->consumo_total / 12 * 3) : 0,
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


    /**
     * Procesa los registros para agregar campos proyectados (cpma_proyectado, msd_proyectado, etc.)
     */
    private function procesarRegistrosProyectados($coleccion)
    {
        return $coleccion->map(function($item) {
            // 1. Calcular suma de los últimos 11 meses (Mes2 a Mes12)
            $sumaUltimos11Meses = $item->Mes2 + $item->Mes3 + $item->Mes4 + $item->Mes5 + 
                                $item->Mes6 + $item->Mes7 + $item->Mes8 + $item->Mes9 + 
                                $item->Mes10 + $item->Mes11 + $item->Mes12;
            $sumaUltimos3meses = $item->Mes10 + $item->Mes11 + $item->Mes12;
            // 2. Contar meses con consumo > 0 (de los últimos 11 meses)
            $mesesConConsumo = 0;
            $meses = [$item->Mes2, $item->Mes3, $item->Mes4, $item->Mes5, $item->Mes6, 
                    $item->Mes7, $item->Mes8, $item->Mes9, $item->Mes10, $item->Mes11, $item->Mes12];
            foreach ($meses as $mes) {
                if ($mes > 0) $mesesConConsumo++;
            }
            
            // 3. Calcular cpma_proyectado
            $sumaTotal = $sumaUltimos11Meses + $item->cpma;
            $divisor = $mesesConConsumo + ($item->cpma > 0 ? 1 : 0);
            
            if ($item->situacion_stock == 'SIN ROTACION') {
                $item->cpma_proyectado = 0;
            } elseif ($sumaTotal == 0 || $divisor == 0) {
                $item->cpma_proyectado = 0;
            } else {
                $item->cpma_proyectado = round($sumaTotal / $divisor, 2);
            }
            
            // 4. Calcular msd_proyectado
            if ($item->cpma_proyectado > 0) {
                $item->msd_proyectado = round($item->stockfinal_proyectado / $item->cpma_proyectado, 2);
            } else {
                $item->msd_proyectado = 0;
            }
            
            // 5. Calcular consumo_cuatro_ult_meses_proyectado
            if (in_array($item->situacion_stock, ['SIN CONSUMO', 'SIN DATOS'])) {
                $item->consumo_cuatro_ult_meses_proyectado = 0;
            } else {
                $item->consumo_cuatro_ult_meses_proyectado = $sumaUltimos3meses + $item->cpma_proyectado;
            }
            
            // 6. Calcular situacion_stock_proyectado
            if ($item->lista_2 == 'Gran Volumen' && $item->msd_proyectado >= 1 && $item->msd_proyectado <= 6) {
                $item->situacion_stock_proyectado = 'NORMOSTOCK';
            } elseif ($item->stockfinal_proyectado > 0 && $item->cpma_proyectado > 0 && $item->msd_proyectado > 6) {
                $item->situacion_stock_proyectado = 'SOBRESTOCK';
            } elseif ($item->stockfinal_proyectado > 0 && $item->cpma_proyectado == 0 && $item->msd_proyectado == 0) {
                $item->situacion_stock_proyectado = 'SIN ROTACION';
            } elseif ($item->stockfinal_proyectado > 0 && $item->cpma_proyectado > 0 && $item->msd_proyectado >= 2 && $item->msd_proyectado <= 6) {
                $item->situacion_stock_proyectado = 'NORMOSTOCK';
            } elseif ($item->stockfinal_proyectado > 0 && $item->cpma_proyectado > 0 && $item->msd_proyectado > 0 && $item->msd_proyectado < 2) {
                $item->situacion_stock_proyectado = 'SUBSTOCK';
            } elseif ($item->stockfinal_proyectado == 0 && $item->cpma_proyectado > 0 && $item->msd_proyectado == 0 && $item->consumo_cuatro_ult_meses_proyectado > 0) {
                $item->situacion_stock_proyectado = 'DESABASTECIDO';
            } elseif ($item->stockfinal_proyectado == 0 && $item->cpma_proyectado > 0 && $item->msd_proyectado == 0 && $item->consumo_cuatro_ult_meses_proyectado == 0) {
                $item->situacion_stock_proyectado = 'SIN CONSUMO';
            } elseif ($item->stockfinal_proyectado == 0 && $item->cpma_proyectado == 0 && $item->msd_proyectado == 0 && $item->consumo_cuatro_ult_meses_proyectado == 0) {
                $item->situacion_stock_proyectado = 'SIN DATOS';
            } else {
                $item->situacion_stock_proyectado = 'SIN CLASIFICAR';
            }
            
            // 7. Calcular envio_sugerido
            $item->envio_sugerido = $this->calcularEnvioSugerido($item);

            return $item;
        });
    }


    /**
     * Calcula el envío sugerido basado en la situación del stock y el tipo de establecimiento
    */
    private function calcularEnvioSugerido($item)
    {
        // Situaciones que no requieren envío
        $situacionesExcluidas = ['SIN ROTACION', 'SIN CONSUMO', 'SIN DATOS'];
        if (in_array($item->situacion_stock_proyectado, $situacionesExcluidas)) {
            return 0;
        }
        
        // Verificar si es establecimiento especial (PIAS, BAP, 024F01)
        $nombreIpress = strtoupper($item->nombre_ipress ?? '');
        $esEstablecimientoEspecial = (
            strpos($nombreIpress, 'PIAS') !== false || 
            strpos($nombreIpress, 'BAP') !== false ||
            $item->cod_ipress == '024F01'
        );
        
        // Si es establecimiento especial: enviar para 2 meses
        if ($esEstablecimientoEspecial) {
            $necesidad = ($item->cpma_proyectado * 2) - $item->stockfinal_proyectado;
            return max(0, round($necesidad, 0));
        }
        
        // Si no es especial y MSD < 3: enviar para 4 meses
        if ($item->msd_proyectado < 3) {
            $necesidad = ($item->cpma_proyectado * 4) - $item->stockfinal_proyectado;
            return max(0, round($necesidad, 0));
        }
        
        // Si MSD >= 3: no enviar
        return 0;
    }



}




