<?php

namespace Database\Seeders;

use App\Models\OfertaAcademica;
use App\Models\PeriodoAcademico;
use Illuminate\Database\Seeder;

class PeriodoAcademicoSeeder extends Seeder
{
    public function run(): void
    {
        $careers = OfertaAcademica::all();

        foreach ($careers as $career) {
            // --- 2025-I (Terminado) ---
            PeriodoAcademico::create([
                'oferta_academica_id' => $career->id,
                'nombre' => "Semestre {$career->codigo} 2025-I",
                'tipo' => 'semestral',
                'fecha_inicio' => '2025-02-01',
                'fecha_fin' => '2025-06-30',
                'fecha_inicio_inscripcion' => '2025-01-01',
                'fecha_fin_inscripcion' => '2025-01-31',
                'fecha_inicio_cierre' => '2025-06-15',
                'fecha_fin_cierre' => '2025-06-30',
                'fecha_inicio_retiro' => '2025-02-15',
                'fecha_fin_retiro' => '2025-03-15',
                'numero_maximo_materias' => 6,
                'estado' => 'terminado',
            ]);

            // --- 2025-II (Terminado) ---
            PeriodoAcademico::create([
                'oferta_academica_id' => $career->id,
                'nombre' => "Semestre {$career->codigo} 2025-II",
                'tipo' => 'semestral',
                'fecha_inicio' => '2025-08-01',
                'fecha_fin' => '2025-12-15',
                'fecha_inicio_inscripcion' => '2025-07-01',
                'fecha_fin_inscripcion' => '2025-07-31',
                'fecha_inicio_cierre' => '2025-12-01',
                'fecha_fin_cierre' => '2025-12-15',
                'fecha_inicio_retiro' => '2025-08-15',
                'fecha_fin_retiro' => '2025-09-15',
                'numero_maximo_materias' => 6,
                'estado' => 'terminado',
            ]);

            // --- 2026-I (Cierre - Activo, no terminado) ---
            PeriodoAcademico::create([
                'oferta_academica_id' => $career->id,
                'nombre' => "Semestre {$career->codigo} 2026-I",
                'tipo' => 'semestral',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-06-30',
                'fecha_inicio_inscripcion' => '2026-01-01',
                'fecha_fin_inscripcion' => '2026-01-31',
                'fecha_inicio_cierre' => '2026-06-15',
                'fecha_fin_cierre' => '2026-06-30',
                'fecha_inicio_retiro' => '2026-02-15',
                'fecha_fin_retiro' => '2026-03-15',
                'numero_maximo_materias' => 6,
                'estado' => 'terminado',
            ]);

            // --- 2026-II (Inscripción) ---
            PeriodoAcademico::create([
                'oferta_academica_id' => $career->id,
                'nombre' => "Semestre {$career->codigo} 2026-II",
                'tipo' => 'semestral',
                'fecha_inicio' => '2026-08-01',
                'fecha_fin' => '2026-12-15',
                'fecha_inicio_inscripcion' => '2026-06-20',
                'fecha_fin_inscripcion' => '2026-07-31',
                'fecha_inicio_cierre' => '2026-12-01',
                'fecha_fin_cierre' => '2026-12-15',
                'fecha_inicio_retiro' => '2026-08-15',
                'fecha_fin_retiro' => '2026-09-15',
                'numero_maximo_materias' => 6,
                'estado' => 'inscripcion',
            ]);
        }
    }
}
