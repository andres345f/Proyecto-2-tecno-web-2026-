<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePeriodoAcademicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'oferta_academica_id' => [
                'required',
                'exists:ofertas_academicas,id',
                function ($attribute, $value, $fail) {
                    $periodo = $this->route('periodoAcademico');
                    $periodoId = is_object($periodo) ? $periodo->id : $periodo;

                    if ($this->input('estado') !== 'terminado') {
                        $activeExists = \App\Models\PeriodoAcademico::where('oferta_academica_id', $value)
                            ->where('id', '!=', $periodoId)
                            ->where('estado', '!=', 'terminado')
                            ->exists();
                        if ($activeExists) {
                            $fail('No se puede guardar este período en un estado activo porque ya existe otro período activo (no terminado) para esta oferta académica.');
                        }
                    }
                }
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'in:semestral,anual'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'fecha_inicio_inscripcion' => ['nullable', 'date'],
            'fecha_fin_inscripcion' => ['nullable', 'date', 'after_or_equal:fecha_inicio_inscripcion'],
            'fecha_inicio_cierre' => ['nullable', 'date'],
            'fecha_fin_cierre' => ['nullable', 'date', 'after_or_equal:fecha_inicio_cierre'],
            'fecha_inicio_retiro' => ['nullable', 'date'],
            'fecha_fin_retiro' => ['nullable', 'date', 'after_or_equal:fecha_inicio_retiro'],
            'numero_maximo_materias' => ['nullable', 'integer', 'min:1'],
            'estado' => ['nullable', 'string', 'in:inscripcion,cierre,retiro,terminado'],
        ];
    }
}
