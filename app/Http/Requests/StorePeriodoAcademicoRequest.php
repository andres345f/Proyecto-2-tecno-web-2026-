<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePeriodoAcademicoRequest extends FormRequest
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
                    $activeExists = \App\Models\PeriodoAcademico::where('oferta_academica_id', $value)
                        ->where('estado', '!=', 'terminado')
                        ->exists();
                    if ($activeExists) {
                        $fail('No se puede crear un nuevo período académico porque ya existe un período activo (no terminado) para esta oferta académica.');
                    }
                }
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'in:semestral,anual,trimestral,cuatrimestral,bimestral,mensual'],
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

    public function messages(): array
    {
        return [
            'oferta_academica_id.required' => 'La oferta académica es obligatoria.',
            'oferta_academica_id.exists' => 'La oferta académica seleccionada no existe.',
            'nombre.required' => 'El nombre del período académico es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'tipo.required' => 'El tipo de período es obligatorio.',
            'tipo.in' => 'El tipo de período seleccionado no es válido.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'fecha_inicio_inscripcion.date' => 'La fecha de inicio de inscripción debe ser una fecha válida.',
            'fecha_fin_inscripcion.date' => 'La fecha de fin de inscripción debe ser una fecha válida.',
            'fecha_fin_inscripcion.after_or_equal' => 'La fecha de fin de inscripción debe ser posterior o igual a la fecha de inicio de inscripción.',
            'fecha_inicio_cierre.date' => 'La fecha de inicio de cierre debe ser una fecha válida.',
            'fecha_fin_cierre.date' => 'La fecha de fin de cierre debe ser una fecha válida.',
            'fecha_fin_cierre.after_or_equal' => 'La fecha de fin de cierre debe ser posterior o igual a la fecha de inicio de cierre.',
            'fecha_inicio_retiro.date' => 'La fecha de inicio de retiro debe ser una fecha válida.',
            'fecha_fin_retiro.date' => 'La fecha de fin de retiro debe ser una fecha válida.',
            'fecha_fin_retiro.after_or_equal' => 'La fecha de fin de retiro debe ser posterior o igual a la fecha de inicio de retiro.',
            'numero_maximo_materias.integer' => 'El número máximo de materias debe ser un número entero.',
            'numero_maximo_materias.min' => 'El número máximo de materias debe ser de al menos 1.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
