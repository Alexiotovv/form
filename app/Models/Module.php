<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Module extends Model
{
    use HasFactory;

    public const REGISTRO_VIEW_ALL_PERMISSION = 'module.registro.view-all';
    public const REGISTRO_DELETE_PERMISSION = 'module.registro.delete';
    public const REGISTRO_PROCESS_PERMISSION = 'module.registro.process';
    public const REGISTRO_ALERTS_PERMISSION = 'module.registro.alerts.view';

    protected $fillable = [
        'name',
        'slug',
        'route_name_index',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function permissionNamesFor(string $slug): array
    {
        $permissions = [
            "module.{$slug}.view",
            "module.{$slug}.create",
            "module.{$slug}.update",
            "module.{$slug}.delete",
        ];

        if ($slug === 'registro') {
            $permissions[] = self::REGISTRO_VIEW_ALL_PERMISSION;
            $permissions[] = self::REGISTRO_PROCESS_PERMISSION;
            $permissions[] = self::REGISTRO_ALERTS_PERMISSION;
        }

        return $permissions;
    }

    public function ensurePermissions(): void
    {
        foreach (self::permissionNamesFor($this->slug) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
