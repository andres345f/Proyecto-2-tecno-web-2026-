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

    public function messages(): array
    {
        return [
            'oferta_academica_id.required' => 'La oferta académica es obligatoria.',
            'oferta_academica_id.exists' => 'La oferta académica seleccionada no existe.',
            'nombre.required' => 'El nombre del plan de pago es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'tipo.required' => 'El tipo de plan de pago es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido (debe ser unico, por_periodo o especial).',
            'monto_matricula.required' => 'El monto de la matrícula es obligatorio.',
            'monto_matricula.numeric' => 'El monto de la matrícula debe ser un valor numérico.',
            'monto_matricula.min' => 'El monto de la matrícula no puede ser menor que 0.',
            'monto_cuota.required' => 'El monto de la cuota es obligatorio.',
            'monto_cuota.numeric' => 'El monto de la cuota debe ser un valor numérico.',
            'monto_cuota.min' => 'El monto de la cuota no puede ser menor que 0.',
            'cantidad_cuotas.required' => 'La cantidad de cuotas es obligatoria.',
            'cantidad_cuotas.integer' => 'La cantidad de cuotas debe ser un número entero.',
            'cantidad_cuotas.min' => 'La cantidad de cuotas debe ser de al menos 1.',
        ];
    }
}
