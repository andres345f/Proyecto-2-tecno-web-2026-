<?php

namespace Database\Seeders;

use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use Illuminate\Database\Seeder;

class MatriculaPeriodoSeeder extends Seeder
{
    public function run(): void
    {
        // Get all career matriculations
        $matriculasCarrera = MatriculaCarrera::all();

        foreach ($matriculasCarrera as $mc) {
            $careerId = $mc->oferta_academica_id;
            
            // Get plans for this career
            $planRegular = PlanPago::where('oferta_academica_id', $careerId)
                ->where('nombre', 'LIKE', 'Regular%')
                ->first();
            $planBeca = PlanPago::where('oferta_academica_id', $careerId)
                ->where('nombre', 'LIKE', 'Beca%')
                ->first();
            
            // 90% regular, 10% beca
            $plan = (rand(1, 10) > 1) ? $planRegular : $planBeca;

            // Check when the student started based on the career matriculation date
            $startYear = $mc->fecha_matricula->year;

            if ($startYear == 2025) {
                // Historical student: enroll in 2025-I, 2025-II, and 2026-I
                $p2025_1 = PeriodoAcademico::where('oferta_academica_id', $careerId)
                    ->where('nombre', 'LIKE', '%2025-I')
                    ->first();
                $p2025_2 = PeriodoAcademico::where('oferta_academica_id', $careerId)
                    ->where('nombre', 'LIKE', '%2025-II')
                    ->first();
                $p2026_1 = PeriodoAcademico::where('oferta_academica_id', $careerId)
                    ->where('nombre', 'LIKE', '%2026-I')
                    ->first();

                if ($p2025_1) {
                    MatriculaPeriodo::create([
                        'matricula_carrera_id' => $mc->id,
                        'periodo_academico_id' => $p2025_1->id,
                        'plan_pago_id' => $plan->id,
                        'fecha_matricula' => '2025-01-28 10:00:00',
                        'estado' => 'completado',
                    ]);
                }

                if ($p2025_2) {
                    MatriculaPeriodo::create([
                        'matricula_carrera_id' => $mc->id,
                        'periodo_academico_id' => $p2025_2->id,
                        'plan_pago_id' => $plan->id,
                        'fecha_matricula' => '2025-07-28 10:00:00',
                        'estado' => 'completado',
                    ]);
                }

                if ($p2026_1) {
                    MatriculaPeriodo::create([
                        'matricula_carrera_id' => $mc->id,
                        'periodo_academico_id' => $p2026_1->id,
                        'plan_pago_id' => $plan->id,
                        'fecha_matricula' => '2026-01-28 10:00:00',
                        'estado' => 'completado',
                    ]);
                }
            } else {
                // New student in 2026: enroll only in 2026-I
                $p2026_1 = PeriodoAcademico::where('oferta_academica_id', $careerId)
                    ->where('nombre', 'LIKE', '%2026-I')
                    ->first();

                if ($p2026_1) {
                    MatriculaPeriodo::create([
                        'matricula_carrera_id' => $mc->id,
                        'periodo_academico_id' => $p2026_1->id,
                        'plan_pago_id' => $plan->id,
                        'fecha_matricula' => '2026-01-28 10:00:00',
                        'estado' => 'completado',
                    ]);
                }
            }
        }
    }
}
