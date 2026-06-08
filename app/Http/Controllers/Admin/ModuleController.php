<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::query()->orderBy('name')->get();

        return view('admin.access.modules.index', compact('modules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('modules', 'slug')],
            'route_name_index' => ['required', 'string', 'max:180', Rule::unique('modules', 'route_name_index')],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $module = Module::create($data);
        $module->ensurePermissions();

        return back()->with('success', 'Modulo creado correctamente.');
    }

    public function update(Request $request, Module $module): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('modules', 'slug')->ignore($module->id)],
            'route_name_index' => ['required', 'string', 'max:180', Rule::unique('modules', 'route_name_index')->ignore($module->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $module->update($data);
        $module->ensurePermissions();

        return back()->with('success', 'Modulo actualizado correctamente.');
    }

    public function syncFromRoutes(): RedirectResponse
    {
        Artisan::call('modules:sync-routes');

        return back()->with('success', 'Modulos sincronizados desde rutas con sufijo .index');
    }
}
