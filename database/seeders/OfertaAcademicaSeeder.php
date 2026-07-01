<?php

namespace Database\Seeders;

use App\Models\OfertaAcademica;
use Illuminate\Database\Seeder;

class OfertaAcademicaSeeder extends Seeder
{
    public function run(): void
    {
        /*
        OfertaAcademica::create([
            'nombre' => 'Técnico Superior en Desarrollo de Software',
            'codigo' => 'TS-DS',
            'descripcion' => 'Carrera técnica enfocada en el diseño, desarrollo, pruebas y mantenimiento de aplicaciones de software.',
        ]);

        OfertaAcademica::create([
            'nombre' => 'Técnico Superior en Redes y Telecomunicaciones',
            'codigo' => 'TS-RT',
            'descripcion' => 'Carrera técnica orientada al diseño, implementación y administración de redes informáticas y sistemas de comunicación.',
        ]);

        OfertaAcademica::create([
            'nombre' => 'Técnico Medio en Diseño Gráfico',
            'codigo' => 'TM-DG',
            'descripcion' => 'Carrera técnica enfocada en la comunicación visual, identidad de marca, ilustración digital y diseño multimedia.',
        ]);
        */

        OfertaAcademica::create([
            'nombre' => 'Técnico Auxiliar en Marketing Digital',
            'codigo' => 'TA-MD',
            'descripcion' => 'Carrera técnica corta dedicada al posicionamiento web, gestión de redes sociales, analítica y publicidad digital.',
        ]);
    }
}
