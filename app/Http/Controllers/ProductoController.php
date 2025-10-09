<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('cod_siga', 'like', "%$search%")
                ->orWhere('cod_sismed', 'like', "%$search%")  
                ->orWhere('codigo_atc', 'like', "%$search%")
                  ->orWhere('cod_unspsc', 'like', "%$search%")
                  ->orWhere('descripcion_sismed', 'like', "%$search%");
            });
        }

        $productos = $query->paginate(50);

        return view('productos.index', compact('productos'));
    }

    public function store(Request $request)
    {
        \App\Models\Producto::create($request->all());
        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente');
    }

    public function update(Request $request, $id)
    {    // Definir reglas de validación basadas en tu migración
        $rules = [
            'cod_unificado' => 'nullable|string|max:6',
            'cod_sismed_analisis' => 'nullable|string|max:6',
            'cod_sismed' => 'nullable|string|max:8',
            'cod_siga' => 'nullable|string|max:13',
            'codigo_atc' => 'nullable|string|max:4',
            'cod_unspsc' => 'nullable|string|max:10',
            'descripcion_sismed' => 'nullable|string|max:180',
            'concentracion' => 'nullable|string|max:50',
            'forma_farmaceutica' => 'nullable|string|max:50',
            'presentacion' => 'nullable|string|max:30',
            'tipo_prod' => 'nullable|string|max:1',
            'lista_1' => 'nullable|string|max:20',
            'lista_2' => 'nullable|string|max:20',
            'tipo_abastecimiento' => 'nullable|string|max:10',
            'estrategico' => 'nullable|string|max:1',
            'biologicos' => 'nullable|string|max:1',
            'odontologicos' => 'nullable|string|max:1',
            'reactivos' => 'nullable|string|max:1',
            'vitales' => 'nullable|string|max:5',
            'peti2023' => 'nullable|string|max:1',
            'peti2018' => 'nullable|string|max:1',
            'peti2015' => 'nullable|string|max:1',
            'peti2012' => 'nullable|string|max:1',
            'peti2010' => 'nullable|string|max:1',
            'venta' => 'nullable|string|max:1',
            'estado' => 'nullable|string|max:1',
            'reg_sanit' => 'nullable|string|max:50',
            'descripcion_siga' => 'nullable|string|max:200',
            'descripcion_cubo' => 'nullable|string|max:200',
            'unidad_medida_x' => 'nullable|string|max:10',
            'descripcion_cubo_2' => 'nullable|string|max:250',
            'descripcion_producto' => 'nullable|string|max:220',
            'descripcion_producto_alt' => 'nullable|string|max:220',
            'descripcion_producto_eca' => 'nullable|string|max:250',
            'unidad_medida_siga' => 'nullable|string|max:25',
            'grupo' => 'nullable|string|max:1',
            'programas' => 'nullable|string|max:50',
            'programas_presupuestales' => 'nullable|string|max:20',
            'producto_fed' => 'nullable|string|max:7',
            'producto_fed_actual' => 'nullable|string|max:10',
            'tipo_indicador_fed' => 'nullable|string|max:5',
            'producto_ap_endis' => 'nullable|string|max:10',
            'anemia' => 'nullable|string|max:10',
            'claves_obstetricas' => 'nullable|string|max:60',
            'clave_azul' => 'nullable|string|max:10',
            'clave_amarilla' => 'nullable|string|max:10',
            'clave_roja' => 'nullable|string|max:10',
            'iras' => 'nullable|string|max:20',
            'iras_menor_12' => 'nullable|string|max:15',
            'edas' => 'nullable|string|max:4',
            'dengue' => 'nullable|string|max:6',
            'dengue_grupo_a' => 'nullable|string|max:50',
            'dengue_grupo_b' => 'nullable|string|max:50',
            'dengue_grupo_c' => 'nullable|string|max:50',
            'malaria' => 'nullable|string|max:7',
            'chikungunya' => 'nullable|string|max:11',
            'zika' => 'nullable|string|max:4',
            'leishmania' => 'nullable|string|max:10',
            'chagas' => 'nullable|string|max:6',
            'ofidismo' => 'nullable|string|max:8',
            'leptospirosis' => 'nullable|string|max:13',
            'planificacion_familiar' => 'nullable|string|max:150',
            'epp' => 'nullable|string|max:5',
            'covid19' => 'nullable|string|max:8',
            'covid19_apoyo_tto' => 'nullable|string|max:15',
            'covid_protocolo_minsa' => 'nullable|string|max:30',
            'pareto' => 'nullable|string|max:1',
            'vital' => 'nullable|string|max:2',
            'convenio_gestion_2020' => 'nullable|string|max:5',
            'convenio_gestion_2021' => 'nullable|string|max:5',
            'producto_cap_eca' => 'nullable|string|max:7',
        ];

        // Mensajes personalizados (opcional, pero recomendado)
        $messages = [
            'convenio_gestion_2021.max' => 'El campo "Convenio Gestión 2021" no debe exceder los 5 caracteres.',
            'producto_cap_eca.max' => 'El campo "Producto CAP ECA" no debe exceder los 7 caracteres.',
            // Puedes agregar más si quieres, pero Laravel ya da un mensaje genérico claro
        ];

        // Validar
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                            ->withErrors($validator)
                            ->withInput();
        }

        try {
            $producto = \App\Models\Producto::findOrFail($id);
            $producto->update($request->all());

            return redirect()->route('productos.index')
                            ->with('success', 'Producto actualizado correctamente.');
        } catch (\Exception $e) {
            // En caso de otro error inesperado (aunque ya no debería ser "data too long")
            return redirect()->back()
                            ->withErrors(['error' => 'Ocurrió un error al actualizar el producto.'])
                            ->withInput();
        }
    }

    public function destroy($id)
    {
        \App\Models\Producto::destroy($id);
        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente');
    }


    public function import(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
        ]);

        $filePath = $request->file('excel_file')->getRealPath();
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($filePath);

        $totalRows = 0;
        $processed = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $index => $row) {
                if ($index === 1) continue; // saltar encabezados

                $cells = $row->toArray(); // ¡Esta es la clave!
                $totalRows++;

                // Validar que cod_sismed no esté vacío
                if (empty($cells[2])) continue;

                $data = [
                    'cod_unificado'          => $cells[0] ?? null,
                    'cod_sismed_analisis'    => $cells[1] ?? null, 
                    'cod_sismed'             => $cells[2] ?? null,
                    'cod_siga'               => $cells[3] ?? null,
                    'codigo_atc'             => $cells[4] ?? null,
                    'cod_unspsc'             => $cells[5] ?? null,
                    'descripcion_sismed'     => $cells[6] ?? null,
                    'concentracion'          => $cells[7] ?? null,
                    'forma_farmaceutica'     => $cells[8] ?? null,
                    'presentacion'           => $cells[9] ?? null,
                    'tipo_prod'              => $cells[10] ?? null,
                    'lista_1'                => $cells[11] ?? null,
                    'lista_2'                => $cells[12] ?? null,
                    'tipo_abastecimiento'    => $cells[13] ?? null,
                    'estrategico'            => $cells[14] ?? null,
                    'biologicos'             => $cells[15] ?? null,
                    'odontologicos'          => $cells[16] ?? null,
                    'reactivos'              => $cells[17] ?? null,
                    'vitales'                => $cells[18] ?? null,
                    'peti2023'               => $cells[19] ?? null,
                    'peti2018'               => $cells[20] ?? null,
                    'peti2015'               => $cells[21] ?? null,
                    'peti2012'               => $cells[22] ?? null,
                    'peti2010'               => $cells[23] ?? null,
                    'venta'                  => $cells[24] ?? null,
                    'estado'                 => $cells[25] ?? null,
                    'reg_sanit'              => $cells[26] ?? null,
                    'descripcion_siga'       => $cells[27] ?? null,
                    'descripcion_cubo'       => $cells[28] ?? null,
                    'unidad_medida_x'        => $cells[29] ?? null,
                    'descripcion_cubo_2'     => $cells[30] ?? null,
                    'descripcion_producto'   => $cells[31] ?? null,
                    'descripcion_producto_alt' => $cells[32] ?? null,
                    'descripcion_producto_eca' => $cells[33] ?? null,
                    'unidad_medida_siga'     => $cells[34] ?? null,
                    'grupo'                  => $cells[35] ?? null,
                    'programas'              => $cells[36] ?? null,
                    'programas_presupuestales' => $cells[37] ?? null,
                    'producto_fed'           => $cells[38] ?? null,
                    'producto_fed_actual'    => $cells[39] ?? null,
                    'tipo_indicador_fed'     => $cells[40] ?? null,
                    'producto_ap_endis'      => $cells[41] ?? null,
                    'anemia'                 => $cells[42] ?? null,
                    'claves_obstetricas'     => $cells[43] ?? null,
                    'clave_azul'             => $cells[44] ?? null,
                    'clave_amarilla'         => $cells[45] ?? null,
                    'clave_roja'             => $cells[46] ?? null,
                    'iras'                   => $cells[47] ?? null,
                    'iras_menor_12'          => $cells[48] ?? null,
                    'edas'                   => $cells[49] ?? null,
                    'dengue'                 => $cells[50] ?? null,
                    'dengue_grupo_a'         => $cells[51] ?? null,
                    'dengue_grupo_b'         => $cells[52] ?? null,
                    'dengue_grupo_c'         => $cells[53] ?? null,
                    'malaria'                => $cells[54] ?? null,
                    'chikungunya'            => $cells[55] ?? null,
                    'zika'                   => $cells[56] ?? null,
                    'leishmania'             => $cells[57] ?? null,
                    'chagas'                 => $cells[58] ?? null,
                    'ofidismo'               => $cells[59] ?? null,
                    'leptospirosis'          => $cells[60] ?? null,
                    'planificacion_familiar' => $cells[61] ?? null,
                    'epp'                    => $cells[62] ?? null,
                    'covid19'                => $cells[63] ?? null,
                    'covid19_apoyo_tto'      => $cells[64] ?? null,
                    'covid_protocolo_minsa'  => $cells[65] ?? null,
                    'pareto'                 => $cells[66] ?? null,
                    'vital'                  => $cells[67] ?? null,
                    'convenio_gestion_2020'  => $cells[68] ?? null,
                    'convenio_gestion_2021'  => $cells[69] ?? null,
                    'producto_cap_eca'       => $cells[70] ?? null,
                ];

                // Usar updateOrCreate en lugar de verificar existencia manualmente
                Producto::updateOrCreate(
                    ['cod_sismed' => $data['cod_sismed']],
                    $data
                );
                $processed++;
            }
        }

        $reader->close();

        return back()->with('success', "Importación completada. Filas procesadas: $processed de $totalRows.");
    }

}
