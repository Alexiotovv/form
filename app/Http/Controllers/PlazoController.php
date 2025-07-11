<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plazo;



class PlazoController extends Controller
{
    public function edit()
    {
        $plazo = Plazo::firstOrCreate([], ['dia_inicio'=>1,'dia_fin'=>5]);
        return view('admin.plazo', compact('plazo'));
    }

    public function update(Request $r)
    {
        $r->validate([
            'dia_inicio' => 'required|integer|between:1,31|lte:dia_fin',
            'dia_fin'    => 'required|integer|between:1,31|gte:dia_inicio',
        ]);

        Plazo::first()->update($r->only('dia_inicio','dia_fin'));
        return back()->with('success','Plazo actualizado');
    }
}
