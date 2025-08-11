<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\FormDet;
use App\Models\Ime1;
use App\Models\Imed2;
use App\Models\Imed3;
use App\Models\Registro;

class ProcesarDbfController extends Controller
{
    public function procesar(Request $request, $registroId)
    {
        $registro = Registro::findOrFail($registroId);
        if ($registro->procesado) {
            return back()->with('warning', 'Este archivo ya fue procesado anteriormente');
        }

        $zipPath = storage_path('app/public/' . $registro->archivo);

        try {
            $djangoConfig = config('services.django');
            DB::beginTransaction();
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $djangoConfig['token'],
            ])->attach(
                'archivo', file_get_contents($zipPath), basename($zipPath)
            )->post($djangoConfig['url'] . '/api/procesar-zip/',[
                'password' => 'VEINTE4512' // Envía la contraseña desde el formulario
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Procesar formDet
                foreach ($data['tablas_procesadas']['formDet'] as $item) {
                    FormDet::create($item);
                }

                // Procesar Ime1
                foreach ($data['tablas_procesadas']['Ime1'] as $item) {
                    Ime1::create($item);
                }

                // Procesar Imed2
                foreach ($data['tablas_procesadas']['Imed2'] as $item) {
                    Imed2::create($item);
                }
                // Procesar Imed3
                foreach ($data['tablas_procesadas']['Imed3'] as $item) {
                    Imed3::create($item);
                }
                DB::commit();
                $registro->update(['procesado' => true]);
                return back()->with('success', 'Datos procesados correctamente');
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
