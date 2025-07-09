<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormKey;

class ClaveAccesoController extends Controller
{
    public function form()
    {
        return view('clave.form');
    }

    public function verificar(Request $request)
    {
        $request->validate(['clave' => 'required']);
        $claveGuardada = FormKey::first();

        if ($claveGuardada && $request->clave === $claveGuardada->clave) {
            session(['clave_correcta' => true]);
            return redirect()->route('registro.create');
        }

        return back()->withErrors(['clave' => 'Clave incorrecta.']);
    }
}
