<?php

namespace App\Services;

use App\Models\GrupoPeriodo;
use App\Models\MallaCurricular;
use App\Models\MatriculaGrupo;
use App\Models\MatriculaPeriodo;
use Illuminate\Validation\ValidationException;

class ValidadorPrerrequisitos
{
    /**
     * Validate that a student can enroll in a group period.
     *
     * @throws ValidationException
     */
    public function validar(MatriculaPeriodo $matriculaPeriodo, GrupoPeriodo $grupoPeriodo): bool
    {
        $grupo = $grupoPeriodo->grupo;
        $materia = $grupo->materia;
        $carrera = $matriculaPeriodo->matriculaCarrera->ofertaAcademica;

        // Check 1: Materia in malla curricular
        $inMalla = MallaCurricular::where('oferta_academica_id', $carrera->id)
            ->where('materia_id', $materia->id)
            ->exists();

        if (! $inMalla) {
            throw ValidationException::withMessages([
                'grupo_id' => 'La materia no pertenece a la malla curricular de tu carrera.',
            ]);
        }

        // Check 2: Prerequisites approved
        $mallaEntry = MallaCurricular::where('oferta_academica_id', $carrera->id)
            ->where('materia_id', $materia->id)
            ->first();

        // Check 2b: Materia already approved by the student in past semesters
        $alreadyPassed = MatriculaGrupo::whereHas('matriculaPeriodo.matriculaCarrera', function ($q) use ($matriculaPeriodo) {
            $q->where('usuario_id', $matriculaPeriodo->matriculaCarrera->usuario_id);
        })
            ->whereHas('grupoPeriodo.grupo', function ($q) use ($materia) {
                $q->where('materia_id', $materia->id);
            })
            ->where('estado', '!=', 'retirado')
            ->where('nota_final', '>=', 51)
            ->exists();

        if ($alreadyPassed) {
            throw ValidationException::withMessages([
                'grupo_id' => "Ya aprobaste esta materia.",
            ]);
        }

        if ($mallaEntry) {
            $prerrequisitos = $mallaEntry->prerrequisitos; // Collection of MallaCurricular

            foreach ($prerrequisitos as $prereqMalla) {
                $prerequisito = $prereqMalla->materia;

                $matriculaGrupo = MatriculaGrupo::whereHas('matriculaPeriodo', function ($q) use ($matriculaPeriodo) {
                    $q->where('matricula_carrera_id', $matriculaPeriodo->matricula_carrera_id);
                })
                    ->whereHas('grupoPeriodo.grupo', function ($q) use ($prerequisito) {
                        $q->where('materia_id', $prerequisito->id);
                    })
                    ->first();

                if (! $matriculaGrupo) {
                    throw ValidationException::withMessages([
                        'grupo_id' => "Falta prerrequisito: {$prerequisito->nombre}.",
                    ]);
                }

                if ($matriculaGrupo->nota_final < 51) {
                    throw ValidationException::withMessages([
                        'grupo_id' => "Prerrequisito no aprobado: {$prerequisito->nombre} (nota: {$matriculaGrupo->nota_final}).",
                    ]);
                }
            }
        }

        // Check 3: Group capacity
        $enrolledCount = MatriculaGrupo::where('grupo_periodo_id', $grupoPeriodo->id)
            ->where('estado', '!=', 'retirado')
            ->count();

        if ($enrolledCount >= $grupoPeriodo->cupo_maximo) {
            throw ValidationException::withMessages([
                'grupo_id' => 'El grupo ha alcanzado su capacidad máxima.',
            ]);
        }

        // Check 4: Not already enrolled
        $alreadyEnrolled = MatriculaGrupo::where('matricula_periodo_id', $matriculaPeriodo->id)
            ->where('grupo_periodo_id', $grupoPeriodo->id)
            ->where('estado', '!=', 'retirado')
            ->exists();

        if ($alreadyEnrolled) {
            throw ValidationException::withMessages([
                'grupo_id' => 'Ya inscrito en este grupo.',
            ]);
        }

        return true;
    }
}
