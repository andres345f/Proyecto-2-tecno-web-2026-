<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'oferta_academica_id' => ['required', 'exists:ofertas_academicas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'in:unico,por_periodo,especial'],
            'monto_matricula' => ['required', 'numeric', 'min:0'],
            'monto_cuota' => ['required', 'numeric', 'min:0'],
            'cantidad_cuotas' => ['required', 'integer', 'min:1'],
        ];
    }
}
