<?php

namespace App\Services;

use App\Models\Cuota;
use App\Models\Materia;
use App\Models\User;

class BuscadorGlobalService
{
    /**
     * Search across students, materias, and cuotas.
     *
     * @return array{usuarios: array, materias: array, cuotas: array}
     */
    public function search(string $query): array
    {
        $sanitized = '%'.$query.'%';

        $usuarios = User::where('is_estudiante', true)
            ->where(function ($q) use ($sanitized) {
                $q->where('name', 'like', $sanitized)
                    ->orWhere('email', 'like', $sanitized);
            })
            ->select('id', 'name as nombre', 'email')
            ->limit(20)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'tipo' => 'student',
            ])
            ->toArray();

        $materias = Materia::where('nombre', 'like', $sanitized)
            ->orWhere('codigo', 'like', $sanitized)
            ->select('id', 'nombre', 'codigo')
            ->limit(20)
            ->get()
            ->map(fn ($materia) => [
                'id' => $materia->id,
                'nombre' => $materia->nombre,
                'codigo' => $materia->codigo,
                'tipo' => 'materia',
            ])
            ->toArray();

        $cuotas = Cuota::where('descripcion', 'like', $sanitized)
            ->select('id', 'descripcion', 'monto')
            ->limit(20)
            ->get()
            ->map(fn ($cuota) => [
                'id' => $cuota->id,
                'descripcion' => $cuota->descripcion,
                'monto' => (float) $cuota->monto,
                'tipo' => 'cuota',
            ])
            ->toArray();

        return [
            'usuarios' => $usuarios,
            'materias' => $materias,
            'cuotas' => $cuotas,
        ];
    }
}
