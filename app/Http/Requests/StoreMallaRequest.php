<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMallaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ofertaId = is_object($this->route('oferta'))
            ? $this->route('oferta')->id
            : $this->route('oferta');

        return [
            'materia_id' => [
                'required',
                'exists:materias,id',
                Rule::unique('malla_curricular', 'materia_id')->where(fn($query) => $query->where('oferta_academica_id', $ofertaId)),
            ],
            'semestre_orden' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'materia_id.required' => 'La materia es obligatoria.',
            'materia_id.exists' => 'La materia seleccionada no existe.',
            'materia_id.unique' => 'Esta materia ya está registrada en la malla curricular de esta oferta académica.',
            'semestre_orden.required' => 'El número de semestre es obligatorio.',
            'semestre_orden.integer' => 'El número de semestre debe ser un número entero.',
            'semestre_orden.min' => 'El número de semestre debe ser al menos 1.',
        ];
    }
}
