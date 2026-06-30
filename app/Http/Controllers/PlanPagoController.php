<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanPagoRequest;
use App\Http\Requests\UpdatePlanPagoRequest;
use App\Models\PlanPago;
use App\Services\PlanPagoService;
use Inertia\Inertia;

class PlanPagoController extends Controller
{
    protected PlanPagoService $planPagoService;

    public function __construct(PlanPagoService $planPagoService)
    {
        $this->planPagoService = $planPagoService;
    }

    public function index()
    {
        $ofertaAcademicaId = request('oferta_academica_id');
        $planes = $this->planPagoService->listarPlanes($ofertaAcademicaId);
        $ofertas = $this->planPagoService->obtenerOfertas();
        return Inertia::render('PlanPago/Index', [
            'planes' => $planes,
            'ofertas' => $ofertas,
        ]);
    }

    public function create()
    {
        $ofertas = $this->planPagoService->obtenerOfertas();
        return Inertia::render('PlanPago/Create', [
            'ofertas' => $ofertas,
        ]);
    }

    public function store(StorePlanPagoRequest $request)
    {
        $this->planPagoService->crearPlan($request->validated());

        return redirect()->route('planes-pago.index');
    }

    public function show(PlanPago $planPago)
    {
        $planConDetalles = $this->planPagoService->cargarDetalles($planPago);
        return Inertia::render('PlanPago/Show', [
            'plan' => $planConDetalles,
        ]);
    }

    public function edit(PlanPago $planPago)
    {
        $ofertas = $this->planPagoService->obtenerOfertas();
        return Inertia::render('PlanPago/Edit', [
            'plan' => $planPago,
            'ofertas' => $ofertas,
        ]);
    }

    public function update(UpdatePlanPagoRequest $request, PlanPago $planPago)
    {
        $this->planPagoService->actualizarPlan($planPago, $request->validated());
        return redirect()->route('planes-pago.index');
    }

    public function destroy(PlanPago $planPago)
    {
        $this->planPagoService->eliminarPlan($planPago);

        return redirect()->route('planes-pago.index');
    }
}
