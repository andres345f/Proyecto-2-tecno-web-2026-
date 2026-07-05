<?php

namespace App\Http\Requests;

use App\Models\GrupoPeriodo;
use Illuminate\Foundation\Http\FormRequest;

class StoreTareaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user || !$user->is_profesor) {
            return false;
        }

        // Validate professor owns the grupo_periodo
        $grupoId = $this->route('grupo');
        if ($grupoId) {
            $grupoPeriodo = GrupoPeriodo::find($grupoId->id ?? $grupoId);
            if ($grupoPeriodo && $grupoPeriodo->docente_id !== $user->id) {
                return false;
            }
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'fecha_vencimiento' => ['required', 'date', 'after:now'],
            'puntaje_maximo' => ['required', 'numeric', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.string' => 'El título debe ser una cadena de texto.',
            'titulo.max' => 'El título no puede tener más de 255 caracteres.',
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a la fecha y hora actual.',
            'puntaje_maximo.required' => 'El puntaje máximo es obligatorio.',
            'puntaje_maximo.numeric' => 'El puntaje máximo debe ser un valor numérico.',
            'puntaje_maximo.min' => 'El puntaje máximo debe ser al menos 1.',
            'puntaje_maximo.max' => 'El puntaje máximo no puede superar los 100 puntos.',
        ];
    }
}
