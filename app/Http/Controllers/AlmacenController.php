<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Illuminate\Http\Request;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AlmacenController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $almacenes = Almacen::when($search, function ($query, $search) {
            $query->where('pliego', 'like', "%{$search}%")
                ->orWhere('cod_ipress', 'like', "%{$search}%")      
                ->orWhere('disa_diresa', 'like', "%{$search}%")
                ->orWhere('departamento', 'like', "%{$search}%")
                ->orWhere('nombre_ipress', 'like', "%{$search}%");
        })->latest()->paginate(50);

        return view('almacenes.index', compact('almacenes', 'search'));
    }

    public function store(Request $request)
    {
        $rules = $this->getValidationRules();
        // Hacer 'cod_ipress' obligatorio en creación
        $rules['cod_ipress'] = 'required|string|max:10';

        $request->validate($rules, $this->getValidationMessages());

        Almacen::create($request->all());
        return redirect()->route('almacenes.index')->with('success', 'Almacén creado correctamente.');
    }

    public function update(Request $request, $id)
    {

        $rules = [
            'cod_pliego' => 'nullable|integer',
            'pliego' => 'nullable|string|max:255',
            'cod_disa' => 'nullable|integer',
            'disa_diresa' => 'nullable|string|max:255',
            'cod_ue_mef' => 'nullable|integer',
            'ue_mef' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:50',
            'ubigeo' => 'nullable|string|max:25',
            'provincia' => 'nullable|string|max:50',
            'distrito' => 'nullable|string|max:50',
            'cod_renipress' => 'nullable|string|max:255',
            'cod_ipress' => 'required|string|max:10',
            'red' => 'nullable|string|max:255',
            'microred' => 'nullable|string|max:255',
            'nombre_ipress' => 'nullable|string|max:100',
            'codigo_nombre_ipress' => 'nullable|string|max:100',
            'nivel' => 'nullable|string|max:10',
            'tipo_establecimiento' => 'nullable|string|max:50',
            'estado_ipress' => 'nullable|string|max:10',
            'universo_ipress' => 'nullable|string|max:2',
            'ipress_feed' => 'nullable|string|max:2',
            'ipress_eca' => 'nullable|string|max:2',
            'ipress_evaluar_disponibilidad' => 'nullable|string|max:2',
            'ipress_dengue' => 'nullable|string|max:7',
            'ipress_prio_temp_bajas' => 'nullable|string|max:2',
            'ipress_prio_riesg_lluv' => 'nullable|string|max:15',
            'est_pert_cuencas' => 'nullable|string|max:10',
            'ipress_prio_plan_malaria' => 'nullable|string|max:2',
            'almacen_pertenece' => 'nullable|string|max:50',
            'filtro' => 'nullable|string|max:6',
            'ruta_distribucion' => 'nullable|string|max:7',
            'monitor' => 'nullable|string|max:100',
            'digitador' => 'nullable|string|max:100',
            'envios' => 'nullable|integer|min:0|max:99',
        ];
         $messages = [
            'estado_ipress.max' => 'El campo "Estado de Ipress" no debe exceder los 10 caracteres.',
            'universo_ipress.max' => 'El campo "Universo Ipress" no debe exceder los 2 caracteres.',
            
            // Puedes agregar más si quieres, pero Laravel ya da un mensaje genérico claro
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }

        try {
            $almacen = \App\Models\Almacen::findOrFail($id);
            $almacen->update($request->all());

        return redirect()->route('almacenes.index')->with('success', 'Almacén actualizado correctamente.');
        } catch (\Exception $e) {
            // En caso de otro error inesperado (aunque ya no debería ser "data too long")
            return redirect()->back()
                            ->withErrors(['error' => 'Ocurrió un error al actualizar el producto.'])
                            ->withInput();
        }
        
    }

    // Reglas de validación reutilizables
    private function getValidationRules()
    {
        return [
            'cod_pliego' => 'nullable|integer',
            'pliego' => 'nullable|string|max:255',
            'cod_disa' => 'nullable|integer',
            'disa_diresa' => 'nullable|string|max:255',
            'cod_ue_mef' => 'nullable|integer',
            'ue_mef' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:50',
            'ubigeo' => 'nullable|string|max:25',
            'provincia' => 'nullable|string|max:50',
            'distrito' => 'nullable|string|max:50',
            'cod_renipress' => 'nullable|string|max:255',
            'cod_ipress' => 'required|string|max:10',
            'red' => 'nullable|string|max:255',
            'microred' => 'nullable|string|max:255',
            'nombre_ipress' => 'nullable|string|max:100',
            'codigo_nombre_ipress' => 'nullable|string|max:100',
            'nivel' => 'nullable|string|max:10',
            'tipo_establecimiento' => 'nullable|string|max:50',
            'estado_ipress' => 'nullable|string|max:10',
            'universo_ipress' => 'nullable|string|max:2',
            'ipress_feed' => 'nullable|string|max:2',
            'ipress_eca' => 'nullable|string|max:2',
            'ipress_evaluar_disponibilidad' => 'nullable|string|max:2',
            'ipress_dengue' => 'nullable|string|max:7',
            'ipress_prio_temp_bajas' => 'nullable|string|max:2',
            'ipress_prio_riesg_lluv' => 'nullable|string|max:15',
            'est_pert_cuencas' => 'nullable|string|max:10',
            'ipress_prio_plan_malaria' => 'nullable|string|max:2',
            'almacen_pertenece' => 'nullable|string|max:50',
            'filtro' => 'nullable|string|max:6',
            'ruta_distribucion' => 'nullable|string|max:7',
            'monitor' => 'nullable|string|max:100',
            'digitador' => 'nullable|string|max:100',
            'envios' => 'nullable|integer|min:0|max:99',
        ];
    }

    // Mensajes personalizados (opcional, pero mejora UX)
    private function getValidationMessages()
    {
        return [
            'cod_ipress.required' => 'El campo COD IPRESS es obligatorio.',
            'cod_ipress.max' => 'El campo COD IPRESS no debe exceder los 10 caracteres.',
            'nombre_ipress.max' => 'El campo NOMBRE IPRESS no debe exceder los 100 caracteres.',
            'ipress_dengue.max' => 'El campo IPRESS DENGUE no debe exceder los 7 caracteres.',
            'filtro.max' => 'El campo FILTRO no debe exceder los 6 caracteres.',
            'ruta_distribucion.max' => 'El campo RUTA DISTRIBUCION no debe exceder los 7 caracteres.',
            // Puedes agregar más si lo deseas
        ];
    }
    
    public function destroy(Almacen $almacen)
    {
        $almacen->delete();
        return redirect()->route('almacenes.index')->with('success', 'Almacén eliminado correctamente');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        $filePath = $request->file('file')->getRealPath();

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($filePath);

        $totalRows = 0;
        $inserted = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $index => $row) {
                // Saltar encabezado
                if ($index === 1) {
                    continue;
                }

                $cells = $row->toArray();

                $totalRows++;

                $data = [
                    'cod_pliego' => !empty($cells[0]) ? (int)$cells[0] : null,
                    'pliego' => $cells[1] ?? '',
                    'cod_disa' => !empty($cells[2]) ? (int)$cells[2] : null,
                    'disa_diresa' => $cells[3] ?? '',
                    'cod_ue_mef' => !empty($cells[4]) ? (int)$cells[4] : null,
                    'ue_mef' => $cells[5] ?? '',
                    'departamento' => $cells[6] ?? '',
                    'ubigeo' => $cells[7] ?? '',
                    'provincia' => $cells[8] ?? '',
                    'distrito' => $cells[9] ?? '',
                    'cod_renipress' => $cells[10] ?? '',
                    'cod_ipress' => $cells[11] ?? '',
                    'red' => $cells[12] ?? '',
                    'microred' => $cells[13] ?? '',
                    'nombre_ipress' => $cells[14] ?? '',
                    'codigo_nombre_ipress' => $cells[15] ?? '',
                    'nivel' => $cells[16] ?? '',
                    'tipo_establecimiento' => $cells[17] ?? '',
                    'estado_ipress' => $cells[18] ?? '',
                    'universo_ipress' => $cells[19] ?? '',
                    'ipress_feed' => $cells[20] ?? '',
                    'ipress_eca' => $cells[21] ?? '',
                    'ipress_evaluar_disponibilidad' => $cells[22] ?? '',
                    'ipress_dengue' => $cells[23] ?? '',
                    'ipress_prio_temp_bajas' => $cells[24] ?? '',
                    'ipress_prio_riesg_lluv' => $cells[25] ?? '',
                    'est_pert_cuencas' => $cells[26] ?? '',
                    'ipress_prio_plan_malaria' => $cells[27] ?? '',
                    'almacen_pertenece' => $cells[28] ?? '',
                    'filtro' => $cells[29] ?? '',
                    'ruta_distribucion' => $cells[30] ?? '',
                    'monitor' => $cells[31] ?? '',
                    'digitador' => $cells[32] ?? '',
                ];

                // Validar que al menos el campo clave no esté vacío (ej. cod_ipress)
                if (empty($data['cod_ipress'])) {
                    continue;
                }

                // Verificar duplicado por cod_ipress
                $exists = \App\Models\Almacen::where('cod_ipress', $data['cod_ipress'])->exists();

                if (!$exists) {
                    \App\Models\Almacen::create($data);
                    $inserted++;
                }
            }
        }

        $reader->close();

        return redirect()->route('almacenes.index')
            ->with('success', "Importación completada. Filas leídas: $totalRows, insertadas: $inserted.");
    }





}
