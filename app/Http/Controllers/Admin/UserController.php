<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Establecimiento; 
use App\Models\Almacen;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
class UserController extends Controller
{
    public function index()
    {
        $almacenes = Almacen::all();
        $users = User::with('roles')->latest()->get();
        return view('admin.users.index', compact('users','almacenes'));
    }

    public function export(): StreamedResponse
    {
        $fileName = 'usuarios_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToFile('php://output');

            $header = WriterEntityFactory::createRowFromArray([
                'ID',
                'Nombre',
                'Email',
                'Rol',
                'Habilitado',
                'Establecimiento',
                'Creado',
            ]);
            $writer->addRow($header);

            User::with(['almacen', 'roles'])->orderBy('id')->chunk(500, function ($users) use ($writer) {
                foreach ($users as $user) {
                    $roles = $user->roles->pluck('name')->join(', ');
                    $writer->addRow(WriterEntityFactory::createRowFromArray([
                        $user->id,
                        $user->name,
                        $user->email,
                        $roles ?: ($user->is_admin ? 'superadmin (legacy)' : 'Sin rol'),
                        $user->is_active ? 'Sí' : 'No',
                        $user->almacen?->nombre_ipress ?? 'Sin asignar',
                        optional($user->created_at)->format('Y-m-d H:i:s'),
                    ]));
                }
            });

            $writer->close();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function toggleStatus(User $user)
    {
        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $message = $user->is_active ? 'Usuario habilitado correctamente.' : 'Usuario deshabilitado correctamente.';

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    public function create()
    {
        $almacenes = Almacen::all();
        return view('admin.users.create', compact('almacenes'));
    }

    public function store(Request $request)
    {
        $admin = $request->has('is_admin') ? 1 : 0;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5|confirmed',
            'almacen_id' => 'required|exists:almacenes,id', // validación FK
        ]);
        
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $admin,
            'is_active' => true,
            'almacen_id' => $request->almacen_id,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {   
        $almacenes = Almacen::all();
        return view('admin.users.edit', compact('user','almacenes'));
    }

    public function update(Request $request, User $user)
    {
        $admin = $request->has('is_admin') ? 1 : 0;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'almacen_id' => 'required|exists:almacenes,id',
        ]);
        
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'is_admin' => $admin,
            'almacen_id' => $request->almacen_id,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }


    public function searchAlmacen(Request $request)
    {
        $q = $request->input('q');

        $results = Almacen::where('cod_ipress', 'LIKE', "%{$q}%")
            ->orWhere('nombre_ipress', 'LIKE', "%{$q}%")
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id, // 🔥 usa el ID real del almacen para guardar en la BD
                    'text' => $item->cod_ipress . ' - ' . $item->nombre_ipress,
                    'red' => $item->red,
                    'microred' => $item->microred,
                ];
            });

        return response()->json($results);
    }

}