<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AlmacenesSeeder;
use Database\Seeders\ProfesionesSeeder;
use Database\Seeders\AdminUserSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {  
        $this->call([
            AlmacenesSeeder::class,
            ProfesionesSeeder::class,
            AdminUserSeeder::class,
        ]);
    
    }


  

}
