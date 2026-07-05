<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalificarEntregaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->is_profesor) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        $entrega = $this->route('entrega');
        $entrega->load('tarea');

        return [
            'nota' => ['required', 'numeric', 'min:0', 'max:'.$entrega->tarea->puntaje_maximo],
            'retroalimentacion' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nota.required' => 'La nota es obligatoria.',
            'nota.numeric' => 'La nota debe ser un valor numérico.',
            'nota.min' => 'La nota no puede ser menor que 0.',
            'nota.max' => 'La nota no puede superar el puntaje máximo de la tarea.',
            'retroalimentacion.string' => 'La retroalimentación debe ser una cadena de texto.',
        ];
    }
}
