<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = Role::findOrCreate('superadmin', 'web');

        User::query()
            ->where('is_admin', true)
            ->get()
            ->each(function (User $user) use ($superadmin) {
                $user->assignRole($superadmin);
            });
    }
}
