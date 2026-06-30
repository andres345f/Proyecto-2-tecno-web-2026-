<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTareaRequest;
use App\Models\GrupoPeriodo;
use App\Models\Tarea;
use Inertia\Inertia;

class TareaController extends Controller
{
    public function index(GrupoPeriodo $grupo)
    {
        $user = auth()->user();

        $tareas = Tarea::where('grupo_periodo_id', $grupo->id)
            ->with(['entregas' => function ($q) use ($user) {
                if ($user->is_estudiante) {
                    $q->where('usuario_id', $user->id);
                }
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Tarea/Index', [
            'grupo' => [
                'id' => $grupo->id,
                'codigo' => $grupo->grupo->codigo,
                'materia' => $grupo->grupo->materia,
            ],
            'tareas' => $tareas,
        ]);
    }

    public function create(GrupoPeriodo $grupo)
    {
        return Inertia::render('Tarea/Create', [
            'grupo' => [
                'id' => $grupo->id,
                'codigo' => $grupo->grupo->codigo,
                'materia' => $grupo->grupo->materia,
            ],
        ]);
    }

    public function store(StoreTareaRequest $request, GrupoPeriodo $grupo)
    {
        $tarea = Tarea::create([
            'grupo_periodo_id' => $grupo->id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'puntaje_maximo' => $request->puntaje_maximo,
        ]);

        return redirect()->route('grupos.tareas.show', [$grupo, $tarea]);
    }

    public function show(GrupoPeriodo $grupo, Tarea $tarea)
    {
        $user = auth()->user();

        $tarea->load(['entregas' => function ($q) use ($user) {
            if ($user->is_estudiante) {
                $q->where('usuario_id', $user->id);
            }
        }, 'entregas.usuario']);

        return Inertia::render('Tarea/Show', [
            'grupo' => [
                'id' => $grupo->id,
                'codigo' => $grupo->grupo->codigo,
                'materia' => $grupo->grupo->materia,
            ],
            'tarea' => $tarea,
        ]);
    }

    public function update(StoreTareaRequest $request, GrupoPeriodo $grupo, Tarea $tarea)
    {
        $tarea->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'puntaje_maximo' => $request->puntaje_maximo,
        ]);

        return redirect()->route('grupos.tareas.show', [$grupo, $tarea]);
    }

    public function destroy(GrupoPeriodo $grupo, Tarea $tarea)
    {
        $tarea->delete();

        return redirect()->route('grupos.tareas.index', $grupo);
    }
}
