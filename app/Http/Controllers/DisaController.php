<?php

namespace App\Http\Controllers;

use App\Models\Disa;
use Illuminate\Http\Request;

class DisaController extends Controller
{
    public function index()
    {
        $disas = Disa::all();
        return view('disas.index', compact('disas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|unique:disas,codigo',
            'nombre' => 'required',
        ]);

        Disa::create($request->all());

        return redirect()->route('disas.index')->with('success', 'DISA creada correctamente.');
    }

    public function update(Request $request, Disa $disa)
    {
        $request->validate([
            'codigo' => 'required|unique:disas,codigo,' . $disa->id,
            'nombre' => 'required',
        ]);

        $disa->update($request->all());

        return redirect()->route('disas.index')->with('success', 'DISA actualizada correctamente.');
    }
}
