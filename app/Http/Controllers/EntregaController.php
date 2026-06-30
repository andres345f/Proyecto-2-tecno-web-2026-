<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalificarEntregaRequest;
use App\Http\Requests\StoreEntregaRequest;
use App\Models\Entrega;
use App\Services\EntregaService;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EntregaController extends Controller
{
    protected EntregaService $entregaService;

    public function __construct(EntregaService $entregaService)
    {
        $this->entregaService = $entregaService;
    }

    public function store(StoreEntregaRequest $request)
    {
        $user = auth()->user();
        $entrega = $this->entregaService->crearEntrega(
            $user->id,
            $request->tarea_id,
            $request->file('archivo')
        );
        return redirect()->route('entregas.show', $entrega);
    }

    public function show(Entrega $entrega)
    {
        $entregaConDetalles = $this->entregaService->cargarDetalles($entrega);
        return Inertia::render('Entrega/Show', [
            'entrega' => $entregaConDetalles,
        ]);
    }

    public function calificar(CalificarEntregaRequest $request, Entrega $entrega)
    {
        $this->entregaService->calificarEntrega($entrega, $request->validated());
        return redirect()->route('entregas.show', $entrega);
    }

    public function download(Entrega $entrega)
    {
        $ruta = $this->entregaService->obtenerRutaDescarga($entrega);
        $filename = basename($ruta);
        return Storage::disk('local')->download($ruta, $filename);
    }
}
