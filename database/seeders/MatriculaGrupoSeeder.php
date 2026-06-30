<?php

namespace Database\Seeders;

use App\Models\GrupoPeriodo;
use App\Models\MatriculaGrupo;
use App\Models\MatriculaPeriodo;
use Illuminate\Database\Seeder;

class MatriculaGrupoSeeder extends Seeder
{
    public function run(): void
    {
        $matriculasPeriodo = MatriculaPeriodo::with(['matriculaCarrera.usuario', 'periodoAcademico'])->get();

        foreach ($matriculasPeriodo as $mp) {
            $user = $mp->matriculaCarrera->usuario;
            $career = $mp->matriculaCarrera->ofertaAcademica;
            $period = $mp->periodoAcademico;
            $startYear = $mp->matriculaCarrera->fecha_matricula->year;

            // Determine semester order based on start year and period name
            $semestreOrden = 1;
            if (str_contains($period->nombre, '2025-I')) {
                $semestreOrden = 1;
            } elseif (str_contains($period->nombre, '2025-II')) {
                $semestreOrden = 2;
            } elseif (str_contains($period->nombre, '2026-I')) {
                $semestreOrden = ($startYear == 2025) ? 3 : 1;
            }

            // Get subjects for this career in this semester
            $materias = $career->materias()
                ->wherePivot('semestre_orden', $semestreOrden)
                ->get();

            foreach ($materias as $materia) {
                // Get group periods offered for this subject in the specific period
                $grupoPeriodos = GrupoPeriodo::where('periodo_academico_id', $period->id)
                    ->whereHas('grupo', function ($query) use ($materia) {
                        $query->where('materia_id', $materia->id);
                    })
                    ->orderBy('id', 'asc')
                    ->get();

                if ($grupoPeriodos->isEmpty()) {
                    continue;
                }

                // Main students (carlos, david, ana) will always enroll in group G1 (index 0) to ensure they share groups.
                // Other students will be distributed between G1 and G2.
                $isMainStudent = in_array($user->email, ['carlos@est.com', 'david@est.com', 'ana@est.com']);
                if ($isMainStudent) {
                    $selected = $grupoPeriodos->first();
                } else {
                    // Distribute other students
                    $selected = ($grupoPeriodos->count() > 1) ? $grupoPeriodos[rand(0, 1)] : $grupoPeriodos->first();
                }

                // Check if already enrolled in this group period (to prevent unique constraint errors)
                $exists = MatriculaGrupo::where('matricula_periodo_id', $mp->id)
                    ->where('grupo_periodo_id', $selected->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Set status and final grade depending on the period status
                if ($period->estado === 'terminado') {
                    // Completed historical semesters: 90% pass, 10% fail
                    $passed = (rand(1, 10) > 1);
                    MatriculaGrupo::create([
                        'matricula_periodo_id' => $mp->id,
                        'grupo_periodo_id' => $selected->id,
                        'nota_final' => $passed ? rand(61, 98) : rand(30, 50),
                        'estado' => $passed ? 'aprobado' : 'reprobado',
                    ]);
                } else {
                    // Current active semester 2026-I: set as 'en_curso'
                    MatriculaGrupo::create([
                        'matricula_periodo_id' => $mp->id,
                        'grupo_periodo_id' => $selected->id,
                        'nota_final' => null,
                        'estado' => 'en_curso',
                    ]);
                }
            }
        }
    }
}
