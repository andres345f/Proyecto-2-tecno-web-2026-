<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMatriculaPeriodoRequest;
use App\Models\MatriculaPeriodo;
use App\Services\MatriculaPeriodoService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class MatriculaPeriodoController extends Controller
{
    protected MatriculaPeriodoService $matriculaPeriodoService;

    public function __construct(MatriculaPeriodoService $matriculaPeriodoService)
    {
        $this->matriculaPeriodoService = $matriculaPeriodoService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $matriculaCarreraId = $request->query('matricula_carrera_id');
        $search = $request->query('search');
        $estado = $request->query('estado');

        $matriculas = $this->matriculaPeriodoService->listarMatriculasPaginadas(
            $user,
            10,
            $matriculaCarreraId ? (int)$matriculaCarreraId : null,
            $search,
            $estado
        );

        $canEnroll = $this->matriculaPeriodoService->determinarCanEnroll($user);

        return Inertia::render('MatriculaPeriodo/Index', [
            'matriculas' => $matriculas,
            'canEnroll' => $canEnroll,
            'filters' => [
                'matricula_carrera_id' => $matriculaCarreraId,
                'search' => $search,
                'estado' => $estado,
            ],
        ]);
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $matriculaCarreraId = $request->query('matricula_carrera_id');

        $data = $this->matriculaPeriodoService->obtenerDatosCreacion(
            $user,
            $matriculaCarreraId ? (int)$matriculaCarreraId : null
        );

        return Inertia::render('MatriculaPeriodo/Create', [
            'matriculaCarrera' => $data['matriculaCarrera'],
            'periodos' => $data['periodos'],
            'planes' => $data['planes'],
        ]);
    }

    public function store(StoreMatriculaPeriodoRequest $request)
    {
        $user = auth()->user();
        $matriculaPeriodo = $this->matriculaPeriodoService->crearMatriculaPeriodo($user, $request->validated());

        if ($user && $user->is_estudiante) {
            return redirect()->route('matriculas-periodo.show', $matriculaPeriodo);
        }

        return redirect()->route('matriculas-carrera.show', $matriculaPeriodo->matricula_carrera_id);
    }

    public function show(MatriculaPeriodo $matriculaPeriodo)
    {
        $user = auth()->user();

        if ($user->is_estudiante && $matriculaPeriodo->matriculaCarrera->usuario_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $matriculaConDetalles = $this->matriculaPeriodoService->cargarDetalles($matriculaPeriodo);

        return Inertia::render('MatriculaPeriodo/Show', [
            'matricula' => $matriculaConDetalles,
        ]);
    }

    public function update(MatriculaPeriodo $matriculaPeriodo)
    {
        if (auth()->user()->is_estudiante) {
            abort(403, 'Unauthorized action.');
        }

        $request = request();
        $request->validate([
            'estado' => 'required|in:activo,inactivo,completado',
        ]);

        $this->matriculaPeriodoService->actualizarEstado($matriculaPeriodo, $request->estado);

        return redirect()->route('matriculas-carrera.show', $matriculaPeriodo->matricula_carrera_id);
    }

    public function destroy(MatriculaPeriodo $matriculaPeriodo)
    {
        if (auth()->user()->is_estudiante) {
            abort(403, 'Unauthorized action.');
        }

        $matriculaCarreraId = $matriculaPeriodo->matricula_carrera_id;
        $this->matriculaPeriodoService->eliminarMatriculaPeriodo($matriculaPeriodo);

        return redirect()->route('matriculas-carrera.show', $matriculaCarreraId);
    }
}
