<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\FormDet;
use App\Models\Ime1;
use App\Models\Imed2;
use App\Models\Imed3;
use App\Models\Registro;
use App\Models\ProcesamientoHistorico;


class ProcesarDbfController extends Controller
{
    // public function procesar(Request $request, $registroId)
    // {
        
    //     $inicio = now();

    //     $registro = Registro::findOrFail($registroId);
    //     if ($registro->procesado) {
    //         return back()->with('warning', 'Este archivo ya fue procesado anteriormente');
    //     }

    //     $zipPath = storage_path('app/public/' . $registro->archivo);

    //     try {
    //         $config = \App\Models\DjangoConfig::first();

    //         if (!$config) {
    //             return back()->with('error', 'No existe configuración de Django API en la BD');
    //         }

    //         DB::beginTransaction();
    //         $response = Http::withHeaders([
    //             'Authorization' => 'Token ' . $config->token,
    //         ])->attach(
    //             'archivo', file_get_contents($zipPath), basename($zipPath)
    //         )->post($config->url . '/api/procesar-zip/',[
    //             'password' => $config->password_zip // Envía la contraseña desde el formulario
    //         ]);

    //         if ($response->successful()) {
    //             $data = $response->json();
    //             $tablas = ['FormDet', 'Ime1', 'Imed2', 'Imed3'];
    //             $cantidad_registros = [];

    //             foreach ($tablas as $tabla) {
    //                 $registros = $data['tablas_procesadas'][$tabla] ?? [];

    //                 if (!empty($registros)) {
    //                     // Insertamos en la BD
    //                     foreach ($registros as $item) {
    //                         $model = "\\App\\Models\\" . $tabla; // construimos el modelo dinámicamente
    //                         $model::create($item);
    //                     }

    //                     // Guardamos el conteo en el arreglo
    //                     $cantidad_registros[] = "📋 {$tabla}: " . count($registros) . " reg.";
    //                 }
    //             }
                

    //             DB::commit();
    //             $registro->update(['procesado' => true]);
                
    //             $fin = now();
    //             $diffMs = $inicio->diffInMilliseconds($fin);
    //             $diffSeg = $inicio->diffInSeconds($fin);
                
    //             $elapsed = "{$diffSeg} seg. {$diffMs} ms";//tiempo_ejecucion
                

    //             ProcesamientoHistorico::create([
    //                 'fecha_ejecucion'   => now(), // fecha y hora actual
    //                 'tiempo_ejecucion'  => $elapsed, // puedes calcular tiempo en segundos o ms
    //                 'tablas_registros'  => implode("\n", $cantidad_registros), // variable
    //                 'user_id'           => Auth::id(), // usuario autenticado
    //             ]);


    //             $mensaje = "✅ Datos procesados correctamente en ⏱️ {$elapsed}.<br>" . implode('<br>', $cantidad_registros);


    //             return back()->with('success', $mensaje);
    //         } else {
    //             DB::rollBack();
    //             return back()->with('error', 'Error al procesar: ' . $response->body());
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Error: ' . $e->getMessage());
    //     }
    // }


    public function procesar(Request $request, $registroId)
    {
        // Log de inicio
        \Log::channel('daily')->info('========== INICIO PROCESAMIENTO ==========');
        \Log::channel('daily')->info('Registro ID: ' . $registroId);
        \Log::channel('daily')->info('Usuario: ' . Auth::user()->email);
        \Log::channel('daily')->info('Fecha/Hora: ' . now());
        
        $inicio = now();

        $registro = Registro::findOrFail($registroId);
        if ($registro->procesado) {
            return back()->with('warning', 'Este archivo ya fue procesado anteriormente');
        }

        $zipPath = storage_path('app/public/' . $registro->archivo);
        
        \Log::channel('daily')->info('Ruta ZIP: ' . $zipPath);
        \Log::channel('daily')->info('Archivo existe: ' . (file_exists($zipPath) ? 'SI' : 'NO'));
        \Log::channel('daily')->info('Tamaño archivo: ' . (file_exists($zipPath) ? filesize($zipPath) . ' bytes' : 'N/A'));

        try {
            $config = \App\Models\DjangoConfig::first();

            if (!$config) {
                \Log::channel('daily')->error('No existe configuración Django');
                return back()->with('error', 'No existe configuración de Django API en la BD');
            }

            \Log::channel('daily')->info('Config Django - URL: ' . $config->url);
            \Log::channel('daily')->info('Config Django - Token: ' . substr($config->token, 0, 10) . '...');
            \Log::channel('daily')->info('Config Django - Password ZIP: ' . ($config->password_zip ? 'Configurada' : 'No configurada'));

            DB::beginTransaction();
            
            $procesamientoHistorico = ProcesamientoHistorico::create([
                'fecha_ejecucion'   => now(),
                'tiempo_ejecucion'  => '0 seg. 0 ms',
                'tablas_registros'  => '',
                'user_id'           => Auth::id(),
            ]);

            \Log::channel('daily')->info('Enviando petición a Django...');
            
            // Obtener el contenido del archivo
            $fileContent = file_get_contents($zipPath);
            \Log::channel('daily')->info('Contenido ZIP leído: ' . strlen($fileContent) . ' bytes');
            
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $config->token,
                'Accept' => 'application/json',
            ])->timeout(300) // 5 minutos de timeout
            ->attach(
                'archivo', 
                $fileContent, 
                basename($zipPath)
            )->post($config->url . '/api/procesar-zip/', [
                'password' => $config->password_zip
            ]);

            \Log::channel('daily')->info('Respuesta recibida de Django');
            \Log::channel('daily')->info('Status: ' . $response->status());
            \Log::channel('daily')->info('Headers: ' . json_encode($response->headers()));
            \Log::channel('daily')->info('Body: ' . $response->body());

            if ($response->successful()) {
                $data = $response->json();
                \Log::channel('daily')->info('Respuesta exitosa: ' . json_encode($data));
                
                $tablas = ['FormDet', 'Ime1', 'Imed2', 'Imed3'];
                $cantidad_registros = [];

                foreach ($tablas as $tabla) {
                    $registros = $data['tablas_procesadas'][$tabla] ?? [];

                    if (!empty($registros)) {
                        foreach ($registros as $item) {
                            $model = "\\App\\Models\\" . $tabla;
                            $model::create($item);
                        }
                        $cantidad_registros[] = "📋 {$tabla}: " . count($registros) . " reg.";
                    }
                }

                $fin = now();
                $diffMs = $inicio->diffInMilliseconds($fin);
                $diffSeg = $inicio->diffInSeconds($fin);
                $elapsed = "{$diffSeg} seg. {$diffMs} ms";

                $procesamientoHistorico->update([
                    'tiempo_ejecucion'  => $elapsed,
                    'tablas_registros'  => implode("\n", $cantidad_registros),
                ]);

                DB::commit();
                $registro->update(['procesado' => true]);

                $mensaje = "✅ Datos procesados correctamente en ⏱️ {$elapsed}.<br>" . implode('<br>', $cantidad_registros);

                \Log::channel('daily')->info('PROCESO EXITOSO: ' . $mensaje);
                
                return back()->with('success', $mensaje);
            } else {
                DB::rollBack();
                \Log::channel('daily')->error('ERROR EN DJANGO API');
                \Log::channel('daily')->error('Status: ' . $response->status());
                \Log::channel('daily')->error('Body: ' . $response->body());
                return back()->with('error', 'Error al procesar: ' . $response->body());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::channel('daily')->error('EXCEPCIÓN EN PROCESAMIENTO');
            \Log::channel('daily')->error('Mensaje: ' . $e->getMessage());
            \Log::channel('daily')->error('Archivo: ' . $e->getFile() . ':' . $e->getLine());
            \Log::channel('daily')->error('Trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }





    // public function procesar(Request $request, $registroId)
    // {
    //     $inicio = now();

    //     $registro = Registro::findOrFail($registroId);
    //     if ($registro->procesado) {
    //         return back()->with('warning', 'Este archivo ya fue procesado anteriormente');
    //     }

    //     $zipPath = storage_path('app/public/' . $registro->archivo);

    //     try {
    //         $config = \App\Models\DjangoConfig::first();

    //         if (!$config) {
    //             return back()->with('error', 'No existe configuración de Django API en la BD');
    //         }

    //         DB::beginTransaction();
            
    //         // PASO 1: Crear el registro histórico (inicialmente sin los datos completos)
    //         $procesamientoHistorico = ProcesamientoHistorico::create([
    //             'fecha_ejecucion'   => now(),
    //             'tiempo_ejecucion'  => '0 seg. 0 ms', // Temporal, se actualizará después
    //             'tablas_registros'  => '', // Temporal, se actualizará después
    //             'user_id'           => Auth::id(),
    //         ]);

    //         $response = Http::withHeaders([
    //             'Authorization' => 'Token ' . $config->token,
    //         ])->attach(
    //             'archivo', file_get_contents($zipPath), basename($zipPath)
    //         )->post($config->url . '/api/procesar-zip/',[
    //             'password' => $config->password_zip
    //         ]);

    //         if ($response->successful()) {
    //             $data = $response->json();
    //             $tablas = ['FormDet', 'Ime1', 'Imed2', 'Imed3'];
    //             $cantidad_registros = [];

    //             foreach ($tablas as $tabla) {
    //                 $registros = $data['tablas_procesadas'][$tabla] ?? [];

    //                 if (!empty($registros)) {
    //                     foreach ($registros as $item) {
    //                         $model = "\\App\\Models\\" . $tabla;
                            
    //                         // PASO 2: Para FormDet, agregar el procesamiento_id
    //                         if ($tabla === 'FormDet') {
    //                             $item['procesamiento_id'] = $procesamientoHistorico->id;
    //                         }
                            
    //                         $model::create($item);
    //                     }

    //                     $cantidad_registros[] = "📋 {$tabla}: " . count($registros) . " reg.";
    //                 }
    //             }

    //             $fin = now();
    //             $diffMs = $inicio->diffInMilliseconds($fin);
    //             $diffSeg = $inicio->diffInSeconds($fin);
    //             $elapsed = "{$diffSeg} seg. {$diffMs} ms";

    //             // PASO 3: Actualizar el registro histórico con los datos completos
    //             $procesamientoHistorico->update([
    //                 'tiempo_ejecucion'  => $elapsed,
    //                 'tablas_registros'  => implode("\n", $cantidad_registros),
    //             ]);

    //             DB::commit();
    //             $registro->update(['procesado' => true]);

    //             $mensaje = "✅ Datos procesados correctamente en ⏱️ {$elapsed}.<br>" . implode('<br>', $cantidad_registros);

    //             return back()->with('success', $mensaje);
    //         } else {
    //             DB::rollBack();
    //             return back()->with('error', 'Error al procesar: ' . $response->body());
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Error: ' . $e->getMessage());
    //     }
    // }
    
}
