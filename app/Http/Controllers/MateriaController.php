<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMateriaRequest;
use App\Http\Requests\UpdateMateriaRequest;
use App\Models\Materia;
use App\Services\MateriaService;
use Inertia\Inertia;

class MateriaController extends Controller
{
    protected MateriaService $materiaService;

    public function __construct(MateriaService $materiaService)
    {
        $this->materiaService = $materiaService;
    }

    public function index()
    {
        $search = request('search');
        $materias = $this->materiaService->listarMaterias($search);

        return Inertia::render('Materia/Index', [
            'materias' => $materias,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create()
    {
        $materias = $this->materiaService->obtenerTodas();

        return Inertia::render('Materia/Create', [
            'materias' => $materias,
        ]);
    }

    public function store(StoreMateriaRequest $request)
    {
        $this->materiaService->crearMateria($request->only(['nombre', 'codigo', 'descripcion']));
        return redirect()->route('materias.index');
    }

    public function show(Materia $materia)
    {
        $materiaConRelaciones = $this->materiaService->cargarRelacionesYPrerrequisitos($materia);
        return Inertia::render('Materia/Show', [
            'materia' => $materiaConRelaciones,
        ]);
    }

    public function edit(Materia $materia)
    {
        return Inertia::render('Materia/Edit', [
            'materia' => $materia,
        ]);
    }

    public function update(UpdateMateriaRequest $request, Materia $materia)
    {
        $this->materiaService->actualizarMateria($materia, $request->only(['nombre', 'codigo', 'descripcion']));
        return redirect()->route('materias.index');
    }

    public function destroy(Materia $materia)
    {
        $this->materiaService->eliminarMateria($materia);
        return redirect()->route('materias.index');
    }
}
