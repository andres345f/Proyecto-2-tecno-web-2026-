<?php

namespace App\Http\Requests;

use App\Models\GrupoPeriodo;
use Illuminate\Foundation\Http\FormRequest;

class StoreTareaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->is_profesor) {
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
}
