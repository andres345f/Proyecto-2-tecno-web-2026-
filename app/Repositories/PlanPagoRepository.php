<?php

namespace App\Repositories;

use App\Models\PlanPago;
use Illuminate\Support\Collection;

class PlanPagoRepository
{
    /**
     * Get filtered payment plans.
     */
    public function obtenerFiltrados(?int $ofertaAcademicaId): Collection
    {
        $query = PlanPago::with('ofertaAcademica');

        if ($ofertaAcademicaId) {
            $query->where('oferta_academica_id', $ofertaAcademicaId);
        }

        return $query->orderBy('nombre')->get();
    }

    /**
     * Store a new payment plan.
     */
    public function guardar(array $data): PlanPago
    {
        return PlanPago::create($data);
    }

    /**
     * Update a payment plan.
     */
    public function actualizar(PlanPago $planPago, array $data): bool
    {
        return $planPago->update($data);
    }

    /**
     * Delete a payment plan.
     */
    public function eliminar(PlanPago $planPago): bool
    {
        return $planPago->delete();
    }
}
