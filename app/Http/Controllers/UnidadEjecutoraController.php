<?php

namespace App\Http\Controllers;

use App\Models\UnidadEjecutora;
use Illuminate\Http\Request;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class UnidadEjecutoraController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');

        $unidades = UnidadEjecutora::when($search, function ($query, $search) {
            $query->where('codigo', 'like', "%{$search}%")
                ->orWhere('ejecutora', 'like', "%{$search}%")
                ->orWhere('pliego', 'like', "%{$search}%")
                ->orWhere('sector', 'like', "%{$search}%");
        })
        ->latest()
        ->paginate(10)
        ->appends(['search' => $search]); // mantiene el valor en paginación

        return view('unidadesejecutoras.index', compact('unidades', 'search'));
    }


    public function create()
    {
        return view('unidadesejecutoras.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|unique:unidades_ejecutoras',
            'ejecutora' => 'required',
            'pliego' => 'required',
            'sector' => 'required',
        ]);

        UnidadEjecutora::create($request->all());

        return redirect()->route('unidadesejecutoras.index')->with('success', 'Unidad Ejecutora creada correctamente.');
    }

    public function edit(UnidadEjecutora $unidade)
    {
        return view('unidadesejecutoras.edit', compact('unidade'));
    }

    public function update(Request $request, UnidadEjecutora $unidade)
    {
        $request->validate([
            'codigo' => 'required|unique:unidades_ejecutoras,codigo,' . $unidade->id,
            'ejecutora' => 'required',
            'pliego' => 'required',
            'sector' => 'required',
        ]);

        $unidade->update($request->all());

        return redirect()->route('unidadesejecutoras.index')->with('success', 'Unidad Ejecutora actualizada correctamente.');
    }

    public function destroy(UnidadEjecutora $unidade)
    {
        $unidade->delete();

        return redirect()->route('unidadesejecutoras.index')->with('success', 'Unidad Ejecutora eliminada correctamente.');
    }


    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $filePath = $request->file('excel_file')->getRealPath();

        $reader = ReaderEntityFactory::createReaderFromFile($filePath);
        $reader->open($filePath);

        $totalRows = 0;
        $inserted = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                // Saltar la cabecera (primera fila)
                if ($rowIndex === 1) {
                    continue;
                }

                $cells = $row->toArray();

                // Esperamos 4 columnas: codigo, ejecutora, pliego, sector
                if (count($cells) < 4) {
                    continue;
                }

                [$codigo, $ejecutora, $pliego, $sector] = $cells;

                // Validar que no estén vacíos
                if (!$codigo || !$ejecutora || !$pliego || !$sector) {
                    continue;
                }

                $totalRows++;

                // Verificar si el código ya existe
                if (!UnidadEjecutora::where('codigo', $codigo)->exists()) {
                    UnidadEjecutora::create([
                        'codigo'     => $codigo,
                        'ejecutora'  => $ejecutora,
                        'pliego'     => $pliego,
                        'sector'     => $sector,
                    ]);
                    $inserted++;
                }
            }
        }

        $reader->close();

        return redirect()->route('unidadesejecutoras.index')
            ->with('success', "Se encontraron {$totalRows} registros en el Excel. Se insertaron {$inserted} nuevos registros.");
    }


}
