<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserPermissionController extends Controller
{
    public function index()
    {
        $users = User::query()->with('roles')->orderBy('name')->get();
        $roles = Role::query()->orderBy('name')->get();

        return view('admin.access.users.index', compact('users', 'roles'));
    }

    public function edit(User $user)
    {
        $roles = Role::query()->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();
        $modules = Module::query()->where('is_active', true)->orderBy('name')->get();

        $userRoleIds = $user->roles->pluck('id')->toArray();
        $userPermissionIds = $user->permissions->pluck('id')->toArray();

        return view('admin.access.users.edit', compact(
            'user',
            'roles',
            'permissions',
            'modules',
            'userRoleIds',
            'userPermissionIds'
        ));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $roleIds = $request->input('roles', []);
        $permissionIds = $request->input('permissions', []);

        $roles = Role::query()->whereIn('id', $roleIds)->pluck('name')->toArray();
        $permissions = Permission::query()->whereIn('id', $permissionIds)->pluck('name')->toArray();

        $user->syncRoles($roles);
        $user->syncPermissions($permissions);

        return redirect()->route('admin.access.users.index')->with('success', 'Roles y permisos del usuario actualizados.');
    }

    public function bulkAssignRoles(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'only_without_role' => ['nullable', 'boolean'],
        ]);

        $role = Role::query()->findOrFail($data['role_id']);

        $users = User::query()
            ->with('roles')
            ->whereIn('id', $data['user_ids'])
            ->get();

        if (! empty($data['only_without_role'])) {
            $users = $users->filter(fn (User $user) => $user->roles->isEmpty());
        }

        foreach ($users as $user) {
            $user->syncRoles([$role->name]);
        }

        return redirect()
            ->route('admin.access.users.index')
            ->with('success', 'Roles asignados masivamente a los usuarios seleccionados.');
    }
}
