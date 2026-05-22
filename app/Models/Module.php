<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Module extends Model
{
    use HasFactory;

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
        return [
            "module.{$slug}.view",
            "module.{$slug}.create",
            "module.{$slug}.update",
            "module.{$slug}.delete",
        ];
    }

    public function ensurePermissions(): void
    {
        foreach (self::permissionNamesFor($this->slug) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
