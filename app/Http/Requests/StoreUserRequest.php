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
}
