<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TMovim;
use App\Models\TMovimDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MovimientosController extends Controller
{
    /**
     * Recibir y guardar movimientos desde archivos DBF
     */
    public function storeMovimientos(Request $request)
    {
        try {
            Log::info('Recibida petición de movimientos', [
                'size' => count($request->movimientos ?? []),
                'data' => $request->all()
            ]);
            
            // Validar la estructura del request
            $request->validate([
                'movimientos' => 'required|array',
                'movimientos.*.movcoditip' => 'nullable|string|max:1',
                'movimientos.*.movnumero' => 'required|string|max:9',
                'movimientos.*.almcodiorg' => 'nullable|string|max:10',  // Actualizado
                'movimientos.*.almorgvir' => 'nullable|string|max:10',   // Actualizado
                'movimientos.*.almcodidst' => 'nullable|string|max:10',
                'movimientos.*.almdstvir' => 'nullable|string|max:10',
                'movimientos.*.movtipodci' => 'nullable|string|max:2',
                'movimientos.*.movnumedci' => 'nullable|string|max:8',
                'movimientos.*.movtipodco' => 'nullable|string|max:2',
                'movimientos.*.movnumedco' => 'nullable|string|max:10',
                'movimientos.*.cctcodigo' => 'nullable|string|max:2',
                'movimientos.*.movtot' => 'nullable|numeric',
                'movimientos.*.prvnumeruc' => 'nullable|string|max:20',  // Actualizado
                'movimientos.*.prvdescrip' => 'nullable|string|max:200', // Actualizado
                'movimientos.*.movrefe' => 'nullable|string|max:200',    // Actualizado
                'movimientos.*.movfechult' => 'nullable|date',
                'movimientos.*.movsitua' => 'nullable|string|max:1',
                'movimientos.*.tip_comp' => 'nullable|string|max:1',
                'movimientos.*.tip_proc' => 'nullable|string|max:2',
                'movimientos.*.num_proc' => 'nullable|string|max:100',
                'movimientos.*.movfecanul' => 'nullable|string|max:30',
            ]);

            $movimientosGuardados = 0;
            $movimientosActualizados = 0;
            $errores = [];

            DB::beginTransaction();

            foreach ($request->movimientos as $index => $movimientoData) {
                try {
                    Log::debug("Procesando movimiento {$index}: " . json_encode($movimientoData));
                    
                    // Verificar si el movimiento ya existe
                    $movimiento = TMovim::where('movnumero', $movimientoData['movnumero'])->first();

                    if ($movimiento) {
                        // Actualizar movimiento existente
                        $movimiento->update($movimientoData);
                        $movimientosActualizados++;
                        Log::debug("Movimiento actualizado: {$movimientoData['movnumero']}");
                    } else {
                        // Crear nuevo movimiento
                        TMovim::create($movimientoData);
                        $movimientosGuardados++;
                        Log::debug("Movimiento creado: {$movimientoData['movnumero']}");
                    }
                } catch (\Exception $e) {
                    Log::error("Error en movimiento {$movimientoData['movnumero']}: " . $e->getMessage());
                    $errores[] = [
                        'movnumero' => $movimientoData['movnumero'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();
            
            Log::info("Movimientos procesados - Guardados: {$movimientosGuardados}, Actualizados: {$movimientosActualizados}, Errores: " . count($errores));

            return response()->json([
                'success' => true,
                'message' => 'Movimientos procesados correctamente',
                'data' => [
                    'guardados' => $movimientosGuardados,
                    'actualizados' => $movimientosActualizados,
                    'errores' => $errores
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar movimientos: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar los movimientos',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Recibir y guardar detalles de movimientos
     */
    public function storeMovimientosDet(Request $request)
    {
        try {
            Log::info('Recibida petición de detalles', [
                'size' => count($request->detalles ?? [])
            ]);
            
            $request->validate([
                'detalles' => 'required|array',
                'detalles.*.movcoditip' => 'nullable|string|max:1',
                'detalles.*.movnumero' => 'required|string|max:9',
                'detalles.*.medcod' => 'nullable|string|max:10',    // Actualizado
                'detalles.*.medlote' => 'nullable|string|max:50',   // Actualizado
                'detalles.*.medfechvto' => 'nullable|date',
                'detalles.*.movcantid' => 'nullable|numeric',
                'detalles.*.movprecio' => 'nullable|numeric',
                'detalles.*.movtotal' => 'nullable|numeric',
                'detalles.*.movfechult' => 'nullable|date',
                'detalles.*.movsitua' => 'nullable|string|max:1',
            ]);

            $detallesGuardados = 0;
            $detallesActualizados = 0;
            $errores = [];

            DB::beginTransaction();

            foreach ($request->detalles as $index => $detalleData) {
                try {
                    Log::debug("Procesando detalle {$index}: " . json_encode($detalleData));
                    
                    $detalle = TMovimDet::where('movnumero', $detalleData['movnumero'])
                        ->where('medcod', $detalleData['medcod'] ?? null)
                        ->where('medlote', $detalleData['medlote'] ?? null)
                        ->first();

                    if ($detalle) {
                        $detalle->update($detalleData);
                        $detallesActualizados++;
                        Log::debug("Detalle actualizado: {$detalleData['movnumero']} - {$detalleData['medcod']}");
                    } else {
                        TMovimDet::create($detalleData);
                        $detallesGuardados++;
                        Log::debug("Detalle creado: {$detalleData['movnumero']} - {$detalleData['medcod']}");
                    }
                } catch (\Exception $e) {
                    Log::error("Error en detalle: " . $e->getMessage());
                    $errores[] = [
                        'movnumero' => $detalleData['movnumero'] ?? 'unknown',
                        'medcod' => $detalleData['medcod'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();
            
            Log::info("Detalles procesados - Guardados: {$detallesGuardados}, Actualizados: {$detallesActualizados}, Errores: " . count($errores));

            return response()->json([
                'success' => true,
                'message' => 'Detalles procesados correctamente',
                'data' => [
                    'guardados' => $detallesGuardados,
                    'actualizados' => $detallesActualizados,
                    'errores' => $errores
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar detalles: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar los detalles',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Recibir ambos archivos en una sola petición
     */
    public function storeMovimientosCompleto(Request $request)
    {
        try {
            Log::info('Recibida petición completa', [
                'has_movimientos' => $request->has('movimientos'),
                'has_detalles' => $request->has('detalles')
            ]);
            
            $resultados = [
                'movimientos' => null,
                'detalles' => null
            ];

            if ($request->has('movimientos')) {
                $movimientosRequest = new Request(['movimientos' => $request->movimientos]);
                $movimientosResponse = $this->storeMovimientos($movimientosRequest);
                $resultados['movimientos'] = $movimientosResponse->getData(true);
            }

            if ($request->has('detalles')) {
                $detallesRequest = new Request(['detalles' => $request->detalles]);
                $detallesResponse = $this->storeMovimientosDet($detallesRequest);
                $resultados['detalles'] = $detallesResponse->getData(true);
            }

            return response()->json([
                'success' => true,
                'message' => 'Datos procesados correctamente',
                'data' => $resultados
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al procesar datos completos: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar los datos',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas
     */
    public function getEstadisticas()
    {
        try {
            $totalMovimientos = TMovim::count();
            $totalDetalles = TMovimDet::count();
            $ultimaActualizacion = TMovim::max('updated_at');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_movimientos' => $totalMovimientos,
                    'total_detalles' => $totalDetalles,
                    'ultima_actualizacion' => $ultimaActualizacion
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en estadísticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}