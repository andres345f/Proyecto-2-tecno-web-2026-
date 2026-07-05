<?php

namespace App\Http\Requests;

use App\Models\GrupoPeriodo;
use App\Models\Horario;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dia' => ['required', 'string', 'in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'aula_id' => ['required', 'exists:aulas,id'],
            'grupo_periodo_id' => ['nullable', 'exists:grupo_periodo,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $dia = $this->input('dia');
            $horaInicio = $this->input('hora_inicio');
            $horaFin = $this->input('hora_fin');
            $aulaId = $this->input('aula_id');
            $grupoPeriodoId = $this->input('grupo_periodo_id');

            if (!$dia || !$horaInicio || !$horaFin || !$aulaId) {
                return;
            }

            $horarioId = $this->route('horario')?->id ?? $this->route('horario');

            // 1. Validar choque de aula
            $aulaConflict = Horario::where('dia', $dia)
                ->where('aula_id', $aulaId)
                ->when($horarioId, function ($query) use ($horarioId) {
                    $query->where('id', '!=', $horarioId);
                })
                ->where(function ($query) use ($horaInicio, $horaFin) {
                    $query->where('hora_inicio', '<', $horaFin)
                        ->where('hora_fin', '>', $horaInicio);
                })
                ->exists();

            if ($aulaConflict) {
                $validator->errors()->add('aula_id', 'El aula seleccionada ya está ocupada en ese día y horario.');
            }

            // 2. Validar choque de docente
            if ($grupoPeriodoId) {
                $grupoPeriodo = GrupoPeriodo::find($grupoPeriodoId);
                if ($grupoPeriodo && $grupoPeriodo->docente_id) {
                    $docenteId = $grupoPeriodo->docente_id;

                    $docenteConflict = Horario::where('dia', $dia)
                        ->when($horarioId, function ($query) use ($horarioId) {
                            $query->where('id', '!=', $horarioId);
                        })
                        ->whereHas('grupoPeriodo', function ($query) use ($docenteId) {
                            $query->where('docente_id', $docenteId);
                        })
                        ->where(function ($query) use ($horaInicio, $horaFin) {
                            $query->where('hora_inicio', '<', $horaFin)
                                ->where('hora_fin', '>', $horaInicio);
                        })
                        ->exists();

                    if ($docenteConflict) {
                        $validator->errors()->add('grupo_periodo_id', 'El docente asignado a este grupo ya tiene una clase registrada en ese día y horario.');
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'dia.required' => 'El día es obligatorio.',
            'dia.string' => 'El día debe ser una cadena de texto.',
            'dia.in' => 'El día seleccionado no es válido.',
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'hora_inicio.date_format' => 'La hora de inicio debe tener el formato HH:MM.',
            'hora_fin.required' => 'La hora de fin es obligatoria.',
            'hora_fin.date_format' => 'La hora de fin debe tener el formato HH:MM.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'aula_id.required' => 'El aula es obligatoria.',
            'aula_id.exists' => 'El aula seleccionada no existe.',
            'grupo_periodo_id.exists' => 'El grupo periodo seleccionado no existe.',
        ];
    }
}
