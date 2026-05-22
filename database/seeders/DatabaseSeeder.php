<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AlmacenesSeeder;
use Database\Seeders\ProfesionesSeeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\AccessControlSeeder;


class DatabaseSeeder extends Seeder
{

    public function run(): void
    {  
        $this->call([
            AlmacenesSeeder::class,
            ProfesionesSeeder::class,
            AdminUserSeeder::class,
            AccessControlSeeder::class,
        ]);
    
    }

}
