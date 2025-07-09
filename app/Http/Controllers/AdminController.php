<?php

namespace App\Http\Controllers;

use App\Models\Registro;

class AdminController extends Controller
{
    public function index()
    {
        $registros = Registro::with('establecimiento', 'profesion')->latest()->get();
        return view('admin.dashboard', compact('registros'));
    }
}

