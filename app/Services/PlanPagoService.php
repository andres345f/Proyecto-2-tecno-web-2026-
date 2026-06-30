<?php

namespace App\Services;

use App\Models\PlanPago;
use App\Repositories\PlanPagoRepository;
use App\Repositories\OfertaAcademicaRepository;
use Illuminate\Support\Collection;

class PlanPagoService
{
    protected PlanPagoRepository $planPagoRepository;
    protected OfertaAcademicaRepository $ofertaRepository;

    public function __construct(
        PlanPagoRepository $planPagoRepository,
        OfertaAcademicaRepository $ofertaRepository
    ) {
        $this->planPagoRepository = $planPagoRepository;
        $this->ofertaRepository = $ofertaRepository;
    }

    /**
     * Get filtered payment plans.
     */
    public function listarPlanes(?int $ofertaAcademicaId): Collection
    {
        return $this->planPagoRepository->obtenerFiltrados($ofertaAcademicaId);
    }

    /**
     * Get academic offerings.
     */
    public function obtenerOfertas(): Collection
    {
        return $this->ofertaRepository->obtenerTodasConMateriasCount();
    }

    /**
     * Create payment plan.
     */
    public function crearPlan(array $data): PlanPago
    {
        return $this->planPagoRepository->guardar($data);
    }

    /**
     * Update payment plan.
     */
    public function actualizarPlan(PlanPago $planPago, array $data): bool
    {
        return $this->planPagoRepository->actualizar($planPago, $data);
    }

    /**
     * Delete payment plan.
     */
    public function eliminarPlan(PlanPago $planPago): bool
    {
        return $this->planPagoRepository->eliminar($planPago);
    }

    /**
     * Load details for a payment plan.
     */
    public function cargarDetalles(PlanPago $planPago): PlanPago
    {
        return $planPago->load('ofertaAcademica');
    }
}
