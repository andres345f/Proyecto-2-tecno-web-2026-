<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userParam = $this->route('user');
        $userId = is_object($userParam) ? $userParam->id : $userParam;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_propietario' => ['boolean'],
            'is_director' => ['boolean'],
            'is_secretaria' => ['boolean'],
            'is_profesor' => ['boolean'],
            'is_estudiante' => ['boolean'],
            'codigo_estudiante' => ['nullable', 'string', 'max:50', Rule::unique('users', 'codigo_estudiante')->ignore($userId)],
            'is_activo' => ['boolean'],
        ];
    }
}
