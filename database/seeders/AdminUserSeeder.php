<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sismed.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('$34Mfasd$AffSdAA') // puedes cambiarlo luego
            ]
        );
    }
}
