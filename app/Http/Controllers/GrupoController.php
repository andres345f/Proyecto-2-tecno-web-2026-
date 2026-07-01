<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGrupoRequest;
use App\Http\Requests\UpdateGrupoRequest;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\OfertaAcademica;
use App\Models\User;
use Inertia\Inertia;

class GrupoController extends Controller
{
    public function index()
    {
        $search = strtolower(request('search'));
        $ofertaId = request('oferta_id');
        $query = Grupo::with(['materia']);

        if ($ofertaId) {
            $query->whereHas('materia.mallaCurricular', function ($q) use ($ofertaId) {
                $q->where('oferta_academica_id', $ofertaId);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(codigo) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('materia', function ($q) use ($search) {
                      $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(codigo) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        $grupos = $query->orderBy('codigo')->paginate(10)->withQueryString();
        $oferta = $ofertaId ? OfertaAcademica::find($ofertaId) : null;

        return Inertia::render('Grupo/Index', [
            'grupos' => $grupos,
            'filters' => request()->only(['search', 'oferta_id']),
            'oferta' => $oferta,
        ]);
    }

    public function create()
    {
        $ofertaId = request('oferta_id');
        $materiasQuery = Materia::orderBy('nombre');
        
        if ($ofertaId) {
            $materiasQuery->whereHas('mallaCurricular', function ($q) use ($ofertaId) {
                $q->where('oferta_academica_id', $ofertaId);
            });
        }

        return Inertia::render('Grupo/Create', [
            'materias' => $materiasQuery->get(),
            'oferta_id' => $ofertaId,
            'oferta' => $ofertaId ? OfertaAcademica::find($ofertaId) : null,
        ]);
    }

    public function store(StoreGrupoRequest $request)
    {
        $grupo = Grupo::create($request->validated());

        return redirect()->route('grupos.index', ['oferta_id' => $request->input('oferta_id')]);
    }

    public function show(Grupo $grupo)
    {
        $grupo->load(['materia', 'grupoPeriodos.periodoAcademico', 'grupoPeriodos.docente']);

        return Inertia::render('Grupo/Show', [
            'grupo' => $grupo,
        ]);
    }

    public function edit(Grupo $grupo)
    {
        $ofertaId = request('oferta_id');
        $materiasQuery = Materia::orderBy('nombre');

        if ($ofertaId) {
            $materiasQuery->whereHas('mallaCurricular', function ($q) use ($ofertaId) {
                $q->where('oferta_academica_id', $ofertaId);
            });
        }

        return Inertia::render('Grupo/Edit', [
            'grupo' => $grupo,
            'materias' => $materiasQuery->get(),
            'oferta_id' => $ofertaId,
            'oferta' => $ofertaId ? OfertaAcademica::find($ofertaId) : null,
        ]);
    }

    public function update(UpdateGrupoRequest $request, Grupo $grupo)
    {
        $grupo->update($request->validated());

        return redirect()->route('grupos.index', ['oferta_id' => $request->input('oferta_id')]);
    }

    public function destroy(Grupo $grupo)
    {
        $ofertaId = request('oferta_id');

        if ($grupo->grupoPeriodos()->exists()) {
            return redirect()->back()->withErrors([
                'error' => 'No se puede eliminar el grupo porque tiene relaciones activas (está asignado a períodos académicos).'
            ]);
        }

        $grupo->delete();

        return redirect()->route('grupos.index', ['oferta_id' => $ofertaId]);
    }

    public function docenteIndex()
    {
        $user = auth()->user();

        if (!$user->is_profesor) {
            abort(403);
        }

        $grupos = \App\Models\GrupoPeriodo::where('docente_id', $user->id)
            ->with(['grupo.materia', 'periodoAcademico', 'horarios.aula'])
            ->get()
            ->map(function ($gp) {
                return [
                    'id' => $gp->id,
                    'codigo' => $gp->grupo->codigo,
                    'materia' => $gp->grupo->materia,
                    'periodo_academico' => $gp->periodoAcademico,
                    'horarios' => $gp->horarios,
                ];
            });

        return Inertia::render('Grupo/DocenteIndex', [
            'grupos' => $grupos,
        ]);
    }

    public function docenteShow($id)
    {
        $user = auth()->user();
        $grupoPeriodo = \App\Models\GrupoPeriodo::findOrFail($id);

        if (!$user->is_profesor || $grupoPeriodo->docente_id !== $user->id) {
            abort(403);
        }

        $grupoPeriodo->load(['grupo.materia', 'docente', 'horarios.aula', 'periodoAcademico']);

        $matriculas = \App\Models\MatriculaGrupo::where('grupo_periodo_id', $grupoPeriodo->id)
            ->where('estado', '!=', 'retirado')
            ->with('matriculaPeriodo.matriculaCarrera.usuario')
            ->get();

        $grupoData = [
            'id' => $grupoPeriodo->id,
            'codigo' => $grupoPeriodo->grupo->codigo,
            'materia' => $grupoPeriodo->grupo->materia,
            'periodo_academico' => $grupoPeriodo->periodoAcademico,
            'docente' => $grupoPeriodo->docente,
            'horarios' => $grupoPeriodo->horarios,
        ];

        return Inertia::render('Grupo/DocenteShow', [
            'grupo' => $grupoData,
            'matriculas' => $matriculas,
        ]);
    }
}
