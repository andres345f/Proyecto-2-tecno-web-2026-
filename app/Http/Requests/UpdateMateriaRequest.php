<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $materiaId = $this->route('materia')->id;

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:50', Rule::unique('materias', 'codigo')->ignore($materiaId)],
            'descripcion' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la materia es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'codigo.required' => 'El código de la materia es obligatorio.',
            'codigo.string' => 'El código debe ser una cadena de texto.',
            'codigo.max' => 'El código no puede tener más de 50 caracteres.',
            'codigo.unique' => 'Este código de materia ya existe en la base de datos.',
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
        ];
    }
}
