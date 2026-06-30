<?php

namespace App\Http\Requests;

use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use Illuminate\Foundation\Http\FormRequest;

class StoreMatriculaPeriodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matricula_carrera_id' => ['required', 'exists:matriculas_carrera,id'],
            'periodo_academico_id' => ['required', 'exists:periodos_academicos,id'],
            'plan_pago_id' => ['required', 'exists:planes_pago,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $matriculaCarrera = MatriculaCarrera::findOrFail($this->matricula_carrera_id);

            $user = $this->user();
            if ($user && $user->is_estudiante) {
                if ($matriculaCarrera->usuario_id !== $user->id) {
                    $validator->errors()->add(
                        'matricula_carrera_id',
                        'Solo puedes matricularte a ti mismo.'
                    );
                }
            }

            // Validate period belongs to same career
            $periodo = PeriodoAcademico::findOrFail($this->periodo_academico_id);
            if ($periodo->oferta_academica_id !== $matriculaCarrera->oferta_academica_id) {
                $validator->errors()->add(
                    'periodo_academico_id',
                    'El período no pertenece a la misma carrera que la matrícula.'
                );
            }

            // Validate plan belongs to same career
            $plan = PlanPago::findOrFail($this->plan_pago_id);
            if ($plan->oferta_academica_id !== $matriculaCarrera->oferta_academica_id) {
                $validator->errors()->add(
                    'plan_pago_id',
                    'El plan de pago no pertenece a la misma carrera que la matrícula.'
                );
            }

            if ($user && $user->is_estudiante && $plan->tipo === 'especial') {
                $validator->errors()->add(
                    'plan_pago_id',
                    'El estudiante no puede matricularse en un plan de pago especial directamente.'
                );
            }

            // Validate no duplicate active enrollment in same period
            $exists = MatriculaPeriodo::where('matricula_carrera_id', $this->matricula_carrera_id)
                ->where('periodo_academico_id', $this->periodo_academico_id)
                ->where('estado', 'activo')
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'periodo_academico_id',
                    'El estudiante ya tiene una matrícula activa para este período.'
                );
            }
        });
    }
}
