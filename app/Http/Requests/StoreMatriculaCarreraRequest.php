<?php

namespace App\Http\Requests;

use App\Models\MatriculaCarrera;
use Illuminate\Foundation\Http\FormRequest;

class StoreMatriculaCarreraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario_id' => ['required', 'exists:users,id'],
            'oferta_academica_id' => ['required', 'exists:ofertas_academicas,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $exists = MatriculaCarrera::where('usuario_id', $this->usuario_id)
                ->where('oferta_academica_id', $this->oferta_academica_id)
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'usuario_id',
                    'This student is already enrolled in this career.'
                );
            }
        });
    }
}
