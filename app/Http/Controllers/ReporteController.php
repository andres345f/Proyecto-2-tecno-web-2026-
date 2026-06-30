<?php

namespace App\Http\Controllers;

use App\Repositories\CuotaRepository;
use App\Repositories\MatriculaRepository;
use App\Repositories\ReporteRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    public function __construct(
        private ReporteRepository $reporteRepo,
        private CuotaRepository $cuotaRepo,
        private MatriculaRepository $matriculaRepo
    ) {
    }

    /**
     * Display the executive reports dashboard.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Reporte/Index', [
            // Existing metrics
            'totalRecaudado' => $this->cuotaRepo->totalRecaudado(),
            'totalPorCobrar' => $this->cuotaRepo->totalPorCobrar(),
            'cuotasVencidasCount' => $this->cuotaRepo->cuotasVencidas()->count(),
            'matriculasPorOferta' => $this->matriculaRepo->matriculasPorOferta(),
            'alumnosPorOferta' => $this->matriculaRepo->alumnosPorOferta(),
            'alumnosDeudores' => $this->cuotaRepo->alumnosDeudores(),
            'totalMatriculasActivas' => $this->matriculaRepo->totalMatriculasActivas(),
            'indiceAprobacion' => $this->reporteRepo->indiceAprobacion(),
            'indiceReprobacion' => $this->reporteRepo->indiceReprobacion(),

            // New advanced KPIs
            'ingresosMensuales' => $this->reporteRepo->ingresosMensuales(),
            'usoMetodosPago' => $this->reporteRepo->usoMetodosPago(),
            'rendimientoPorMateria' => $this->reporteRepo->rendimientoPorMateria(),
            'estadisticasTareas' => $this->reporteRepo->estadisticasTareas(),
            'visitasActivas' => $this->reporteRepo->visitasActivas(),
            'paginasMasVisitadas' => $this->reporteRepo->paginasMasVisitadas(),
            'ocupacionGrupos' => $this->reporteRepo->ocupacionGrupos(),
        ]);
    }
}
