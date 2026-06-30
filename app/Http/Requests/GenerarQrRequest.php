<?php

namespace App\Http\Requests;

use App\Models\Cuota;
use Illuminate\Foundation\Http\FormRequest;

class GenerarQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user->is_estudiante) {
            return false;
        }

        // If cuota_id is provided, validate ownership
        if ($this->has('cuota_id') && $this->cuota_id) {
            $cuota = Cuota::find($this->cuota_id);

            if (! $cuota) {
                return true; // Let rules() handle exists validation
            }

            $matriculaPeriodo = $cuota->matriculaPeriodo;
            if (! $matriculaPeriodo) {
                return false;
            }

            $matriculaCarrera = $matriculaPeriodo->matriculaCarrera;
            if (! $matriculaCarrera || $matriculaCarrera->usuario_id !== $user->id) {
                return false;
            }
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'cuota_id' => ['required', 'exists:cuotas,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $cuota = Cuota::find($this->cuota_id);

            if (! $cuota) {
                return;
            }

            // Cuota must be pending
            if ($cuota->estado !== 'pendiente') {
                $validator->errors()->add('cuota_id', 'Esta cuota ya está pagada.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'cuota_id.required' => 'El ID de la cuota es requerido.',
            'cuota_id.exists' => 'La cuota seleccionada no existe.',
        ];
    }
}
