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
}
