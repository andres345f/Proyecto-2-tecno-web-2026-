<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHorarioRequest;
use App\Http\Requests\UpdateHorarioRequest;
use App\Models\Horario;
use App\Services\HorarioService;
use Inertia\Inertia;

class HorarioController extends Controller
{
    protected HorarioService $horarioService;

    public function __construct(HorarioService $horarioService)
    {
        $this->horarioService = $horarioService;
    }

    public function index()
    {
        $horarios = $this->horarioService->listarHorarios();

        return Inertia::render('Horario/Index', [
            'horarios' => $horarios,
        ]);
    }

    public function create()
    {
        return Inertia::render('Horario/Create');
    }

    public function store(StoreHorarioRequest $request)
    {
        $this->horarioService->crearHorario($request->validated());
        return redirect()->back();
    }

    public function show(Horario $horario)
    {
        $horarioConDetalles = $this->horarioService->cargarDetalles($horario);
        return Inertia::render('Horario/Show', [
            'horario' => $horarioConDetalles,
        ]);
    }

    public function edit(Horario $horario)
    {
        return Inertia::render('Horario/Edit', [
            'horario' => $horario,
        ]);
    }

    public function update(UpdateHorarioRequest $request, Horario $horario)
    {
        $this->horarioService->actualizarHorario($horario, $request->validated());
        return redirect()->back();
    }

    public function destroy(Horario $horario)
    {
        $this->horarioService->eliminarHorario($horario);
        return redirect()->back();
    }
}
