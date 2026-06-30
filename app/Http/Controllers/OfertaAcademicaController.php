<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOfertaAcademicaRequest;
use App\Http\Requests\UpdateOfertaAcademicaRequest;
use App\Models\OfertaAcademica;
use App\Services\OfertaAcademicaService;
use Inertia\Inertia;

class OfertaAcademicaController extends Controller
{
    protected OfertaAcademicaService $ofertaService;

    public function __construct(OfertaAcademicaService $ofertaService)
    {
        $this->ofertaService = $ofertaService;
    }

    public function index()
    {
        $ofertas = $this->ofertaService->listarOfertas();

        return Inertia::render('OfertaAcademica/Index', [
            'ofertas' => $ofertas,
        ]);
    }

    public function create()
    {
        return Inertia::render('OfertaAcademica/Create');
    }

    public function store(StoreOfertaAcademicaRequest $request)
    {
        $this->ofertaService->crearOferta($request->validated());
        return redirect()->route('ofertas-academicas.index');
    }

    public function show(OfertaAcademica $oferta)
    {
        $ofertaConDetalles = $this->ofertaService->cargarDetalles($oferta);
        $allMaterias = $this->ofertaService->obtenerTodasLasMaterias();
        return Inertia::render('OfertaAcademica/Show', [
            'oferta' => $ofertaConDetalles,
            'allMaterias' => $allMaterias,
        ]);
    }

    public function edit(OfertaAcademica $oferta)
    {
        return Inertia::render('OfertaAcademica/Edit', [
            'oferta' => $oferta,
        ]);
    }

    public function update(UpdateOfertaAcademicaRequest $request, OfertaAcademica $oferta)
    {
        $this->ofertaService->actualizarOferta($oferta, $request->validated());
        return redirect()->route('ofertas-academicas.index');
    }

    public function destroy(OfertaAcademica $oferta)
    {
        $this->ofertaService->eliminarOferta($oferta);
        return redirect()->route('ofertas-academicas.index');
    }
}
