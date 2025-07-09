<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Establecimiento;

class EstablecimientosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Establecimiento::create(['nombre' => 'Centro Salud Lima']);
        Establecimiento::create(['nombre' => 'Hospital Regional Norte']);
        Establecimiento::create(['nombre' => 'Clínica San Gabriel']);
    }
}
