<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckFormKey
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::get('clave_correcta')) {
            return redirect()->route('acceso.form')->with('error', 'Debe ingresar la clave.');
        }

        return $next($request);
    }
}
