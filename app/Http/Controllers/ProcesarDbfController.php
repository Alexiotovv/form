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
    public function procesar(Request $request, $registroId)
    {
        
        $inicio = now();

        $registro = Registro::findOrFail($registroId);
        if ($registro->procesado) {
            return back()->with('warning', 'Este archivo ya fue procesado anteriormente');
        }

        $zipPath = storage_path('app/public/' . $registro->archivo);

        try {
            $config = \App\Models\DjangoConfig::first();

            if (!$config) {
                return back()->with('error', 'No existe configuración de Django API en la BD');
            }

            DB::beginTransaction();
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $config->token,
            ])->attach(
                'archivo', file_get_contents($zipPath), basename($zipPath)
            )->post($config->url . '/api/procesar-zip/',[
                'password' => $config->password_zip // Envía la contraseña desde el formulario
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $tablas = ['FormDet', 'Ime1', 'Imed2', 'Imed3'];
                $cantidad_registros = [];

                foreach ($tablas as $tabla) {
                    $registros = $data['tablas_procesadas'][$tabla] ?? [];

                    if (!empty($registros)) {
                        // Insertamos en la BD
                        foreach ($registros as $item) {
                            $model = "\\App\\Models\\" . $tabla; // construimos el modelo dinámicamente
                            $model::create($item);
                        }

                        // Guardamos el conteo en el arreglo
                        $cantidad_registros[] = "📋 {$tabla}: " . count($registros) . " reg.";
                    }
                }

                DB::commit();
                $registro->update(['procesado' => true]);
                
                $fin = now();
                $diffMs = $inicio->diffInMilliseconds($fin);
                $diffSeg = $inicio->diffInSeconds($fin);
                
                $elapsed = "{$diffSeg} seg. {$diffMs} ms";//tiempo_ejecucion
                

                ProcesamientoHistorico::create([
                    'fecha_ejecucion'   => now(), // fecha y hora actual
                    'tiempo_ejecucion'  => $elapsed, // puedes calcular tiempo en segundos o ms
                    'tablas_registros'  => implode("\n", $cantidad_registros), // variable
                    'user_id'           => Auth::id(), // usuario autenticado
                ]);


                $mensaje = "✅ Datos procesados correctamente en ⏱️ {$elapsed}.<br>" . implode('<br>', $cantidad_registros);


                return back()->with('success', $mensaje);
            } else {
                DB::rollBack();
                return back()->with('error', 'Error al procesar: ' . $response->body());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
