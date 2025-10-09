<?php

namespace App\Http\Controllers;

use App\Models\DjangoConfig;
use Illuminate\Http\Request;

class DjangoConfigController extends Controller
{
    public function index()
    {
        $config = DjangoConfig::first();
        return view('django_config.index', compact('config'));
    }

    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'token' => 'required|string',
            'password_zip' => 'required|string',
        ]);

        $config = DjangoConfig::first();

        if ($config) {
            $config->update($request->all());
        } else {
            DjangoConfig::create($request->all());
        }

        return back()->with('success', 'Configuración guardada correctamente');
    }
}
