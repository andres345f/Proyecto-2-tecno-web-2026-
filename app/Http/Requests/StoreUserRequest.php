<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'is_propietario' => ['boolean'],
            'is_director' => ['boolean'],
            'is_secretaria' => ['boolean'],
            'is_profesor' => ['boolean'],
            'is_estudiante' => ['boolean'],
            'codigo_estudiante' => ['nullable', 'string', 'max:50', 'unique:users,codigo_estudiante'],
            'is_activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.string' => 'El correo electrónico debe ser una cadena de texto.',
            'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
            'email.max' => 'El correo electrónico no puede tener más de 255 caracteres.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.string' => 'La contraseña debe ser una cadena de texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'is_propietario.boolean' => 'El campo propietario debe ser verdadero o falso.',
            'is_director.boolean' => 'El campo director debe ser verdadero o falso.',
            'is_secretaria.boolean' => 'El campo secretaria debe ser verdadero o falso.',
            'is_profesor.boolean' => 'El campo profesor debe ser verdadero o falso.',
            'is_estudiante.boolean' => 'El campo estudiante debe ser verdadero o falso.',
            'codigo_estudiante.string' => 'El código de estudiante debe ser una cadena de texto.',
            'codigo_estudiante.max' => 'El código de estudiante no puede tener más de 50 caracteres.',
            'codigo_estudiante.unique' => 'Este código de estudiante ya está registrado.',
            'is_activo.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }
}
