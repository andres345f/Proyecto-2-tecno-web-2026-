<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMatriculaGrupoRequest;
use App\Models\MatriculaGrupo;
use App\Services\MatriculaGrupoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MatriculaGrupoController extends Controller
{
    protected MatriculaGrupoService $service;

    public function __construct(MatriculaGrupoService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->obtenerDatosIndex(auth()->user());

        return Inertia::render('MatriculaGrupo/Index', $data);
    }

    public function create()
    {
        $matriculaPeriodoId = request('matricula_periodo_id');
        $data = $this->service->obtenerDatosCrear(auth()->user(), $matriculaPeriodoId ? intval($matriculaPeriodoId) : null);

        return Inertia::render('MatriculaGrupo/Create', $data);
    }

    public function store(StoreMatriculaGrupoRequest $request)
    {
        $this->service->inscribirEstudianteAGrupos(
            auth()->user(),
            intval($request->matricula_periodo_id),
            $request->grupo_ids
        );

        return redirect()->route('matriculas-grupo.index')->with('success', 'Inscripciones a grupos realizadas con éxito.');
    }

    public function show($id)
    {
        $data = $this->service->obtenerDetalles(auth()->user(), intval($id));

        return Inertia::render('MatriculaGrupo/Show', $data);
    }

    public function descargarPlantillaNotas($grupoId)
    {
        if (auth()->user()->is_estudiante) {
            abort(403, 'Unauthorized.');
        }

        $res = $this->service->generarPlantillaNotas(intval($grupoId));

        return response()->stream($res['callback'], 200, $res['headers']);
    }

    public function importarNotas(Request $request, $grupoId)
    {
        if (auth()->user()->is_estudiante) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt',
        ]);

        $res = $this->service->importarNotas($request->file('archivo'), intval($grupoId));

        if (!$res['success']) {
            if (isset($res['import_errors'])) {
                return redirect()->back()->withErrors(['import_errors' => $res['import_errors']]);
            }
            return redirect()->back()->withErrors($res['errors']);
        }

        return redirect()->back()->with('success', "Se cargaron correctamente las notas de {$res['count']} estudiantes.");
    }

    public function destroy(MatriculaGrupo $matriculaGrupo)
    {
        $this->service->retirarMatriculaGrupo($matriculaGrupo);

        return redirect()->route('matriculas-grupo.index');
    }
}
