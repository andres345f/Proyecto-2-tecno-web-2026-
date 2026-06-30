<?php

namespace Database\Seeders;

use App\Models\Grupo;
use App\Models\GrupoPeriodo;
use App\Models\PeriodoAcademico;
use App\Models\Tarea;
use Illuminate\Database\Seeder;

class TareaSeeder extends Seeder
{
    public function run(): void
    {
        $activePeriodDS = PeriodoAcademico::where('nombre', 'like', '%DS%2026-I%')->first();
        $activePeriodDG = PeriodoAcademico::where('nombre', 'like', '%DG%2026-I%')->first();

        $prog2Grupo = Grupo::where('codigo', 'DS-301-G1')->first(); // Programación II (Carlos, David)
        $prog1Grupo = Grupo::where('codigo', 'DS-101-G1')->first(); // Introducción a la Prog
        $designGrupo = Grupo::where('codigo', 'DG-301-G1')->first(); // Diseño Editorial (Ana)

        $prog2GP = $prog2Grupo && $activePeriodDS ? GrupoPeriodo::where('grupo_id', $prog2Grupo->id)->where('periodo_academico_id', $activePeriodDS->id)->first() : null;
        $prog1GP = $prog1Grupo && $activePeriodDS ? GrupoPeriodo::where('grupo_id', $prog1Grupo->id)->where('periodo_academico_id', $activePeriodDS->id)->first() : null;
        $designGP = $designGrupo && $activePeriodDG ? GrupoPeriodo::where('grupo_id', $designGrupo->id)->where('periodo_academico_id', $activePeriodDG->id)->first() : null;

        if ($prog2GP) {
            Tarea::create([
                'grupo_periodo_id' => $prog2GP->id,
                'titulo' => 'Parcial 1 - Programación II',
                'descripcion' => 'Primer examen parcial. Implementación de patrones de diseño Creacionales y Estructurales.',
                'fecha_vencimiento' => now()->addWeek(),
                'puntaje_maximo' => 100,
            ]);

            Tarea::create([
                'grupo_periodo_id' => $prog2GP->id,
                'titulo' => 'Práctica 1 - Patrones GoF',
                'descripcion' => 'Desarrollar un sistema de facturación aplicando el patrón Factory Method y Singleton.',
                'fecha_vencimiento' => now()->addDays(14),
                'puntaje_maximo' => 50,
            ]);
        }

        if ($prog1GP) {
            Tarea::create([
                'grupo_periodo_id' => $prog1GP->id,
                'titulo' => 'Tarea 1 - Estructuras de Control',
                'descripcion' => 'Resolver los 10 ejercicios propuestos de bucles y condicionales.',
                'fecha_vencimiento' => now()->addDays(10),
                'puntaje_maximo' => 30,
            ]);
        }

        if ($designGP) {
            Tarea::create([
                'grupo_periodo_id' => $designGP->id,
                'titulo' => 'Proyecto 1 - Retícula Editorial',
                'descripcion' => 'Diseño de la retícula base y maquetación de las primeras 4 páginas de una revista.',
                'fecha_vencimiento' => now()->addWeek(),
                'puntaje_maximo' => 100,
            ]);
        }
    }
}
