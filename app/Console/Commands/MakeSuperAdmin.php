<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class MakeSuperAdmin extends Command
{
    protected $signature = 'user:make-superadmin {email : Correo del usuario}';

    protected $description = 'Asigna el rol superadmin a un usuario por email';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            $this->error("No existe usuario con email: {$email}");
            return self::FAILURE;
        }

        $role = Role::findOrCreate('superadmin', 'web');
        $user->assignRole($role);

        // Compatibilidad con codigo legacy basado en is_admin
        if (! (bool) $user->is_admin) {
            $user->is_admin = true;
            $user->save();
        }

        $this->info("Usuario {$user->email} promovido a superadmin.");

        return self::SUCCESS;
    }
}
