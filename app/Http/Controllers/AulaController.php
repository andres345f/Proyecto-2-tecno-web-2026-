<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAulaRequest;
use App\Http\Requests\UpdateAulaRequest;
use App\Models\Aula;
use App\Services\AulaService;
use Inertia\Inertia;

class AulaController extends Controller
{
    protected AulaService $aulaService;

    public function __construct(AulaService $aulaService)
    {
        $this->aulaService = $aulaService;
    }

    public function index()
    {
        $search = request('search');
        $aulas = $this->aulaService->listarAulas($search);

        return Inertia::render('Aula/Index', [
            'aulas' => $aulas,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Aula/Create');
    }

    public function store(StoreAulaRequest $request)
    {
        $this->aulaService->crearAula($request->validated());

        return redirect()->route('aulas.index');
    }

    public function show(Aula $aula)
    {
        $aulaConDetalles = $this->aulaService->cargarDetalles($aula);
        $periodos = \App\Models\PeriodoAcademico::with('ofertaAcademica')->orderBy('fecha_inicio', 'desc')->get();

        return Inertia::render('Aula/Show', [
            'aula' => $aulaConDetalles,
            'periodos' => $periodos,
        ]);
    }

    public function edit(Aula $aula)
    {
        return Inertia::render('Aula/Edit', [
            'aula' => $aula,
        ]);
    }

    public function update(UpdateAulaRequest $request, Aula $aula)
    {
        $this->aulaService->actualizarAula($aula, $request->validated());

        return redirect()->route('aulas.index');
    }

    public function destroy(Aula $aula)
    {
        $this->aulaService->eliminarAula($aula);

        return redirect()->route('aulas.index');
    }
}
