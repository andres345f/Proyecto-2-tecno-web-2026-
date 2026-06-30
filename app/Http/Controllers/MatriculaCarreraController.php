<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMatriculaCarreraRequest;
use App\Models\MatriculaCarrera;
use App\Services\MatriculaCarreraService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class MatriculaCarreraController extends Controller
{
    protected MatriculaCarreraService $matriculaCarreraService;

    public function __construct(MatriculaCarreraService $matriculaCarreraService)
    {
        $this->matriculaCarreraService = $matriculaCarreraService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->query('search');
        $estado = $request->query('estado');

        $matriculas = $this->matriculaCarreraService->listarMatriculasPaginadas($user, 10, $search, $estado);

        return Inertia::render('MatriculaCarrera/Index', [
            'matriculas' => $matriculas,
            'filters' => [
                'search' => $search,
                'estado' => $estado,
            ],
        ]);
    }

    public function create()
    {
        $data = $this->matriculaCarreraService->obtenerDatosFormulario();

        return Inertia::render('MatriculaCarrera/Create', [
            'usuarios' => $data['usuarios'],
            'ofertas' => $data['ofertas'],
        ]);
    }

    public function store(StoreMatriculaCarreraRequest $request)
    {
        $this->matriculaCarreraService->crearMatricula($request->validated());

        return redirect()->route('matriculas-carrera.index');
    }

    public function show(MatriculaCarrera $matriculaCarrera)
    {
        $matriculaConDetalles = $this->matriculaCarreraService->cargarDetalles($matriculaCarrera);

        return Inertia::render('MatriculaCarrera/Show', [
            'matricula' => $matriculaConDetalles,
        ]);
    }

    public function update(MatriculaCarrera $matriculaCarrera)
    {
        $request = request();
        $request->validate([
            'estado' => 'required|in:activo,inactivo,retirado',
        ]);

        $this->matriculaCarreraService->actualizarEstado($matriculaCarrera, $request->estado);
        return redirect()->route('matriculas-carrera.index');
    }

    public function destroy(MatriculaCarrera $matriculaCarrera)
    {
        $this->matriculaCarreraService->eliminarMatricula($matriculaCarrera);
        return redirect()->route('matriculas-carrera.index');
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt',
        ]);

        $result = $this->matriculaCarreraService->importarEstudiantesYMatriculas($request->file('archivo'));

        if (!$result['success']) {
            if (isset($result['import_errors'])) {
                return redirect()->back()->withErrors(['import_errors' => $result['import_errors']]);
            }
            return redirect()->back()->withErrors($result['errors']);
        }

        return redirect()->route('matriculas-carrera.index')->with('success', "Se importaron correctamente {$result['count']} estudiantes y sus matrículas.");
    }

    public function descargarPlantilla()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_matriculas.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['nombre', 'email', 'codigo_oferta']);
            fputcsv($file, ['Juan Perez', 'juan.perez@example.com', 'INF-01']);
            fputcsv($file, ['Maria Gomez', 'maria.gomez@example.com', 'INF-01']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
