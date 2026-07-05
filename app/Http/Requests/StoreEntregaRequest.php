<?php

namespace App\Http\Requests;

use App\Models\Entrega;
use App\Models\MatriculaGrupo;
use App\Models\Tarea;
use Illuminate\Foundation\Http\FormRequest;

class StoreEntregaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->is_estudiante) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'tarea_id' => ['required', 'exists:tareas,id'],
            'archivo' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $tarea = Tarea::with('grupoPeriodo')->find($this->tarea_id);

            if (! $tarea) {
                return;
            }

            // Validate student is enrolled in the tarea's grupo_periodo
            $enrolled = MatriculaGrupo::whereHas('matriculaPeriodo', function ($q) {
                $q->whereHas('matriculaCarrera', function ($q2) {
                    $q2->where('usuario_id', $this->user()->id);
                });
            })
                ->where('grupo_periodo_id', $tarea->grupo_periodo_id)
                ->exists();

            if (! $enrolled) {
                $validator->errors()->add(
                    'tarea_id',
                    'No estás inscrito en este grupo.'
                );
            }

            // Validate not past deadline
            if ($tarea->fecha_vencimiento->isPast()) {
                $validator->errors()->add(
                    'tarea_id',
                    'La fecha límite de entrega ha pasado.'
                );
            }

            // Validate not duplicate submission
            $existingEntrega = Entrega::where('tarea_id', $this->tarea_id)
                ->where('usuario_id', $this->user()->id)
                ->exists();

            if ($existingEntrega) {
                $validator->errors()->add(
                    'tarea_id',
                    'Ya has entregado esta tarea.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'tarea_id.required' => 'La tarea es obligatoria.',
            'tarea_id.exists' => 'La tarea seleccionada no existe.',
            'archivo.required' => 'El archivo es obligatorio.',
            'archivo.file' => 'Debe subir un archivo válido.',
            'archivo.max' => 'El archivo no puede pesar más de 10 MB.',
            'archivo.mimes' => 'El archivo debe tener uno de los siguientes formatos: pdf, doc, docx.',
        ];
    }
}
