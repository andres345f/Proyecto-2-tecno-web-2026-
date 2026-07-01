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
    public function totalRecaudado($periodoId = null, $fechaInicio = null, $fechaFin = null): float
    {
        $query = Pago::where('estado', 'completado');
        if ($periodoId) {
            $query->whereHas('cuota.matriculaPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
        }
        return (float) $query->sum('monto_pagado');
    }

    /**
     * Total amount pending from cuotas in 'pendiente' state.
     */
    public function totalPorCobrar($periodoId = null, $fechaInicio = null, $fechaFin = null): float
    {
        $query = Cuota::where('estado', 'pendiente');
        if ($periodoId) {
            $query->whereHas('matriculaPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_vencimiento', [$fechaInicio, $fechaFin]);
        }
        return (float) $query->sum('monto');
    }

    /**
     * Cuotas that are overdue (pendiente AND fecha_vencimiento < now).
     */
    public function cuotasVencidas($periodoId = null, $fechaInicio = null, $fechaFin = null): Collection
    {
        $query = Cuota::where('estado', 'pendiente')
            ->where('fecha_vencimiento', '<', now());
        if ($periodoId) {
            $query->whereHas('matriculaPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_vencimiento', [$fechaInicio, $fechaFin]);
        }
        return $query->with('matriculaPeriodo.matriculaCarrera.usuario')
            ->get();
    }

    /**
     * Students with overdue cuotas, grouped by student.
     * Returns collection of users with their total overdue amount.
     */
    public function alumnosDeudores($periodoId = null, $fechaInicio = null, $fechaFin = null): Collection
    {
        $query = Cuota::where('estado', 'pendiente')
            ->where('fecha_vencimiento', '<', now());
        if ($periodoId) {
            $query->whereHas('matriculaPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_vencimiento', [$fechaInicio, $fechaFin]);
        }
        $vencidas = $query->with('matriculaPeriodo.matriculaCarrera.usuario')
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
