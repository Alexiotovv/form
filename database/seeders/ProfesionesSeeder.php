<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Profesion;

class ProfesionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
           $profesiones = [
        'Químico Farmacéutico',
        'Técnico Laboratorio',
        'Digitador',
        'Técnico Farmacéutico',
        'Ingeniero de Sistemas',
        'Médico',
        'Técnico en Enfermería',
        'Técnico Informático',
        'Enfermera',
    ];

    foreach ($profesiones as $nombre) {
        Profesion::create(['nombre_profesion' => $nombre]);
    }
    }
}
