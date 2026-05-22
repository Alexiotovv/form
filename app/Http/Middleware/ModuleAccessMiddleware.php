<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($user->hasRole('superadmin') || (bool) $user->is_admin) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (! $routeName || ! str_ends_with($routeName, '.index')) {
            return $next($request);
        }

        $module = Module::query()
            ->where('route_name_index', $routeName)
            ->where('is_active', true)
            ->first();

        if (! $module) {
            return $next($request);
        }

        $permission = "module.{$module->slug}.view";
        if (! $user->can($permission)) {
            abort(403, 'No tienes permiso para acceder a este modulo.');
        }

        return $next($request);
    }
}
