<?php

namespace App\Repositories;

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Support\Collection;

class CuotaRepository
{
    /**
     * Total amount collected from completed pagos.
     */
    public function totalRecaudado(): float
    {
        return (float) Pago::where('estado', 'completado')
            ->sum('monto_pagado');
    }

    /**
     * Total amount pending from cuotas in 'pendiente' state.
     */
    public function totalPorCobrar(): float
    {
        return (float) Cuota::where('estado', 'pendiente')
            ->sum('monto');
    }

    /**
     * Cuotas that are overdue (pendiente AND fecha_vencimiento < now).
     */
    public function cuotasVencidas(): Collection
    {
        return Cuota::where('estado', 'pendiente')
            ->where('fecha_vencimiento', '<', now())
            ->with('matriculaPeriodo.matriculaCarrera.usuario')
            ->get();
    }

    /**
     * Students with overdue cuotas, grouped by student.
     * Returns collection of users with their total overdue amount.
     */
    public function alumnosDeudores(): Collection
    {
        $vencidas = Cuota::where('estado', 'pendiente')
            ->where('fecha_vencimiento', '<', now())
            ->with('matriculaPeriodo.matriculaCarrera.usuario')
            ->get();

        $grouped = $vencidas->groupBy(function ($cuota) {
            return $cuota->matriculaPeriodo->matriculaCarrera->usuario_id;
        });

        return $grouped->map(function ($cuotas, $userId) {
            $usuario = $cuotas->first()->matriculaPeriodo->matriculaCarrera->usuario;

            return [
                'user_id' => $userId,
                'nombre' => $usuario->name,
                'email' => $usuario->email,
                'total_deuda' => $cuotas->sum('monto'),
                'cuotas_vencidas' => $cuotas->count(),
            ];
        })->values();
    }
}
