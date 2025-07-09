<?php
namespace App\Http\Controllers;

use App\Models\FormKey;
use Illuminate\Http\Request;

class FormKeyController extends Controller
{
    public function edit()
    {
        $clave = FormKey::first();
        return view('admin.clave', compact('clave'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'clave' => 'required|string|min:4'
        ]);

        FormKey::updateOrCreate([], ['clave' => $request->clave]);

        return back()->with('success', 'Clave actualizada correctamente.');
    }
}
