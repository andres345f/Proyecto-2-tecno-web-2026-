<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGrupoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:50', 'unique:grupos,codigo'],
            'materia_id' => ['required', 'exists:materias,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.string' => 'El código debe ser una cadena de texto.',
            'codigo.max' => 'El código no puede tener más de 50 caracteres.',
            'codigo.unique' => 'Este código ya existe en la base de datos.',
            'materia_id.required' => 'La materia es obligatoria.',
            'materia_id.exists' => 'La materia seleccionada no existe.',
        ];
    }
}
