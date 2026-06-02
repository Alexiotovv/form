<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Almacen;
use App\Models\User;

class UserBulkController extends Controller
{
    public function index()
    {
        $almacenes = Almacen::query()
            ->whereNotIn('id', function ($query) {
                $query->select('almacen_id')
                    ->from('users')
                    ->whereNotNull('almacen_id');
            })
            ->orderBy('cod_ipress')
            ->get();

        return view('admin.users.bulk', compact('almacenes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:5',
            'almacen_ids' => 'required|array|min:1',
            'almacen_ids.*' => 'exists:almacenes,id',
        ]);

        $password = $request->input('password');
        $almacenIds = $request->input('almacen_ids', []);

        $created = 0;
        $skipped = 0;

        foreach ($almacenIds as $id) {
            $almacen = Almacen::find($id);
            if (! $almacen) continue;

            // Seguridad extra: no crear si el almacen ya tiene usuario asociado.
            if (User::where('almacen_id', $almacen->id)->exists()) {
                $skipped++;
                continue;
            }

            $nombre = $almacen->nombre_ipress ?? '';
            // Obtener desde el 10mo caracter (1-based). En mb_substr el offset 9.
            $usernamePart = mb_substr($nombre, 9);
            $usernamePart = mb_strtolower($usernamePart);
            $usernamePart = preg_replace('/\s+/', '_', $usernamePart);
            $usernamePart = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $usernamePart) ?: $usernamePart;
            $usernamePart = preg_replace('/[^a-z_]/', '', $usernamePart);
            $usernamePart = trim($usernamePart, '_');

            if ($usernamePart === '') {
                $usernamePart = 'usuario';
            }

            $email = $usernamePart . '@sismed.com';

            // Si el email ya existe, saltar para evitar duplicados
            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            User::create([
                'name' => $nombre ?: $email,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => 0,
                'is_active' => true,
                'almacen_id' => $almacen->id,
            ]);

            $created++;
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Usuarios creados: $created. Saltados (existentes): $skipped.");
    }
}
