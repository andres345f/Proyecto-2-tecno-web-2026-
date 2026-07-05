<?php

namespace App\Http\Requests;

use App\Models\MatriculaPeriodo;
use Illuminate\Foundation\Http\FormRequest;

class StoreMatriculaGrupoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matricula_periodo_id' => ['required', 'exists:matriculas_periodo,id'],
            'grupo_ids' => ['required', 'array', 'min:1'],
            'grupo_ids.*' => ['exists:grupo_periodo,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            // Validate matricula_periodo belongs to the student's matricula_carrera
            $matriculaPeriodo = MatriculaPeriodo::with(['matriculaCarrera', 'periodoAcademico'])->find($this->matricula_periodo_id);

            if ($matriculaPeriodo && $this->user()->is_estudiante && $matriculaPeriodo->matriculaCarrera->usuario_id !== $this->user()->id) {
                $validator->errors()->add(
                    'matricula_periodo_id',
                    'Este período de matrícula no le pertenece.'
                );
            }

            // Validate matricula_periodo is active
            if ($matriculaPeriodo && $matriculaPeriodo->estado !== 'activo') {
                $validator->errors()->add(
                    'matricula_periodo_id',
                    'Este período de matrícula no está activo.'
                );
            }

            if ($matriculaPeriodo) {
                $periodoAcademico = $matriculaPeriodo->periodoAcademico;
                $maxMaterias = $periodoAcademico->numero_maximo_materias;

                // Validate limit of materias in the period
                $alreadyEnrolledCount = \App\Models\MatriculaGrupo::where('matricula_periodo_id', $matriculaPeriodo->id)
                    ->where('estado', '!=', 'retirado')
                    ->count();

                $newEnrollmentsCount = count($this->grupo_ids);

                if (($alreadyEnrolledCount + $newEnrollmentsCount) > $maxMaterias) {
                    $validator->errors()->add(
                        'grupo_ids',
                        "No puedes inscribir más de {$maxMaterias} materias en este período. Ya tienes {$alreadyEnrolledCount} inscrita(s) e intentas inscribir {$newEnrollmentsCount} más."
                    );
                }

                // Validate that the user did not choose the same materia multiple times in the selection
                $materiaIds = \App\Models\GrupoPeriodo::whereIn('grupo_periodo.id', $this->grupo_ids)
                    ->join('grupos', 'grupo_periodo.grupo_id', '=', 'grupos.id')
                    ->pluck('grupos.materia_id')
                    ->toArray();

                if (count($materiaIds) !== count(array_unique($materiaIds))) {
                    $validator->errors()->add(
                        'grupo_ids',
                        'No puedes inscribirte a más de un grupo para la misma materia.'
                    );
                }

                // Validate that the user is not already enrolled in these materias
                $alreadyEnrolledMateriaIds = \App\Models\MatriculaGrupo::where('matricula_periodo_id', $matriculaPeriodo->id)
                    ->where('estado', '!=', 'retirado')
                    ->join('grupo_periodo', 'matriculas_grupo.grupo_periodo_id', '=', 'grupo_periodo.id')
                    ->join('grupos', 'grupo_periodo.grupo_id', '=', 'grupos.id')
                    ->pluck('grupos.materia_id')
                    ->toArray();

                foreach ($materiaIds as $materiaId) {
                    if (in_array($materiaId, $alreadyEnrolledMateriaIds)) {
                        $materia = \App\Models\Materia::find($materiaId);
                        $validator->errors()->add(
                            'grupo_ids',
                            "Ya estás inscrito en la materia: {$materia->nombre}."
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'matricula_periodo_id.required' => 'El periodo de matrícula es obligatorio.',
            'matricula_periodo_id.exists' => 'El periodo de matrícula seleccionado no existe.',
            'grupo_ids.required' => 'Debe seleccionar al menos un grupo.',
            'grupo_ids.array' => 'Los grupos seleccionados deben ser un arreglo.',
            'grupo_ids.min' => 'Debe seleccionar al menos un grupo.',
            'grupo_ids.*.exists' => 'Uno de los grupos seleccionados no es válido.',
        ];
    }
}
