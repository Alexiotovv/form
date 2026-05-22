<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::query()->with('permissions')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();
        $modules = Module::query()->orderBy('name')->get();

        return view('admin.access.roles.index', compact('roles', 'permissions', 'modules'));
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('roles', 'name')],
        ]);

        Role::create(['name' => $data['name'], 'guard_name' => 'web']);

        return back()->with('success', 'Rol creado correctamente.');
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        $permissionIds = $request->input('permissions', []);
        $permissions = Permission::query()->whereIn('id', $permissionIds)->pluck('name')->toArray();

        $role->syncPermissions($permissions);

        return back()->with('success', "Permisos actualizados para el rol {$role->name}.");
    }

    public function destroyRole(Role $role): RedirectResponse
    {
        if ($role->name === 'superadmin') {
            return back()->with('error', 'No se puede eliminar el rol superadmin.');
        }

        $role->delete();

        return back()->with('success', 'Rol eliminado correctamente.');
    }
}
