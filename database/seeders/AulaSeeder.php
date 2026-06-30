<?php

namespace Database\Seeders;

use App\Models\Aula;
use Illuminate\Database\Seeder;

class AulaSeeder extends Seeder
{
    public function run(): void
    {
        $aulas = [
            ['nombre' => 'Aula 101', 'codigo' => 'AUL-101', 'capacidad' => 35],
            ['nombre' => 'Aula 102', 'codigo' => 'AUL-102', 'capacidad' => 30],
            ['nombre' => 'Aula 103', 'codigo' => 'AUL-103', 'capacidad' => 30],
            ['nombre' => 'Aula 104', 'codigo' => 'AUL-104', 'capacidad' => 30],
            ['nombre' => 'Aula 201', 'codigo' => 'AUL-201', 'capacidad' => 40],
            ['nombre' => 'Aula 202', 'codigo' => 'AUL-202', 'capacidad' => 40],
            ['nombre' => 'Aula 203', 'codigo' => 'AUL-203', 'capacidad' => 40],
            ['nombre' => 'Aula 204', 'codigo' => 'AUL-204', 'capacidad' => 45],
            ['nombre' => 'Aula 301', 'codigo' => 'AUL-301', 'capacidad' => 40],
            ['nombre' => 'Aula 302', 'codigo' => 'AUL-302', 'capacidad' => 40],
            ['nombre' => 'Aula 303', 'codigo' => 'AUL-303', 'capacidad' => 40],
            ['nombre' => 'Aula 304', 'codigo' => 'AUL-304', 'capacidad' => 40],
            ['nombre' => 'Aula 305', 'codigo' => 'AUL-305', 'capacidad' => 40],
            ['nombre' => 'Aula 401', 'codigo' => 'AUL-401', 'capacidad' => 40],
            ['nombre' => 'Aula 402', 'codigo' => 'AUL-402', 'capacidad' => 40],
            ['nombre' => 'Aula 403', 'codigo' => 'AUL-403', 'capacidad' => 40],
            ['nombre' => 'Aula 404', 'codigo' => 'AUL-404', 'capacidad' => 40],
            ['nombre' => 'Aula 405', 'codigo' => 'AUL-405', 'capacidad' => 40],
            ['nombre' => 'Aula Magna', 'codigo' => 'AUL-MAG', 'capacidad' => 100],
            ['nombre' => 'Laboratorio A', 'codigo' => 'LAB-A', 'capacidad' => 25],
            ['nombre' => 'Laboratorio B', 'codigo' => 'LAB-B', 'capacidad' => 20],
            ['nombre' => 'Laboratorio C', 'codigo' => 'LAB-C', 'capacidad' => 25],
            ['nombre' => 'Laboratorio D', 'codigo' => 'LAB-D', 'capacidad' => 25],
            ['nombre' => 'Laboratorio E', 'codigo' => 'LAB-E', 'capacidad' => 25],
            ['nombre' => 'Laboratorio F', 'codigo' => 'LAB-F', 'capacidad' => 25],
            ['nombre' => 'Laboratorio G', 'codigo' => 'LAB-G', 'capacidad' => 25],
            ['nombre' => 'Aula Híbrida 1', 'codigo' => 'AUL-HIB1', 'capacidad' => 30],
            ['nombre' => 'Aula Híbrida 2', 'codigo' => 'AUL-HIB2', 'capacidad' => 30],
            ['nombre' => 'Aula de Innovación', 'codigo' => 'AUL-INOV', 'capacidad' => 35],
            ['nombre' => 'Aula de Proyectos', 'codigo' => 'AUL-PROY', 'capacidad' => 35],
        ];

        foreach ($aulas as $aula) {
            Aula::create($aula);
        }
    }
}
