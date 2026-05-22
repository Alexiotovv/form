<?php

namespace App\Console\Commands;

use App\Models\Module;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class SyncModulesFromRoutes extends Command
{
    protected $signature = 'modules:sync-routes {--only-active : Actualiza solo los modulos ya existentes}';

    protected $description = 'Sincroniza modulos desde rutas con nombre terminado en .index';

    public function handle(): int
    {
        $onlyActive = (bool) $this->option('only-active');
        $count = 0;
        $extraIndexRoutes = [
            'monitor.tmovim',
        ];

        foreach (Route::getRoutes() as $route) {
            $routeName = $route->getName();
            if (! $routeName) {
                continue;
            }

            $isIndexRoute = str_ends_with($routeName, '.index') || in_array($routeName, $extraIndexRoutes, true);
            if (! $isIndexRoute) {
                continue;
            }

            $methods = $route->methods();
            if (! in_array('GET', $methods, true)) {
                continue;
            }

            $base = str_ends_with($routeName, '.index')
                ? Str::beforeLast($routeName, '.index')
                : $routeName;
            $slug = Str::slug(str_replace('.', '-', $base));
            $name = Str::headline(str_replace('.', ' ', $base));

            $existing = Module::query()->where('route_name_index', $routeName)->first();
            if ($onlyActive && ! $existing) {
                continue;
            }

            $module = Module::updateOrCreate(
                ['route_name_index' => $routeName],
                [
                    'name' => $existing?->name ?? $name,
                    'slug' => $existing?->slug ?? $slug,
                    'description' => $existing?->description,
                    'is_active' => $existing?->is_active ?? true,
                ]
            );

            $module->ensurePermissions();
            $count++;
        }

        $this->info("Sincronizacion completada. Modulos procesados: {$count}");

        return self::SUCCESS;
    }
}
