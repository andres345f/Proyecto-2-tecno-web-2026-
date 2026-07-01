<?php

namespace App\Http\Controllers;

use App\Models\PeriodoAcademico;
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
        $periodoId = $request->input('periodo_academico_id');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        return Inertia::render('Reporte/Index', [
            // Available filters list
            'periodos' => PeriodoAcademico::orderBy('nombre', 'desc')->get(),

            // Active filters values
            'filters' => [
                'periodo_academico_id' => $periodoId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],

            // Existing metrics (with filters applied)
            'totalRecaudado' => $this->cuotaRepo->totalRecaudado($periodoId, $fechaInicio, $fechaFin),
            'totalPorCobrar' => $this->cuotaRepo->totalPorCobrar($periodoId, $fechaInicio, $fechaFin),
            'cuotasVencidasCount' => $this->cuotaRepo->cuotasVencidas($periodoId, $fechaInicio, $fechaFin)->count(),
            'matriculasPorOferta' => $this->matriculaRepo->matriculasPorOferta($periodoId, $fechaInicio, $fechaFin),
            'alumnosPorOferta' => $this->matriculaRepo->alumnosPorOferta($periodoId, $fechaInicio, $fechaFin),
            'alumnosDeudores' => $this->cuotaRepo->alumnosDeudores($periodoId, $fechaInicio, $fechaFin),
            'totalMatriculasActivas' => $this->matriculaRepo->totalMatriculasActivas($periodoId, $fechaInicio, $fechaFin),
            'indiceAprobacion' => $this->reporteRepo->indiceAprobacion($periodoId, $fechaInicio, $fechaFin),
            'indiceReprobacion' => $this->reporteRepo->indiceReprobacion($periodoId, $fechaInicio, $fechaFin),

            // New advanced KPIs (with filters applied)
            'ingresosMensuales' => $this->reporteRepo->ingresosMensuales($periodoId, $fechaInicio, $fechaFin),
            'usoMetodosPago' => $this->reporteRepo->usoMetodosPago($periodoId, $fechaInicio, $fechaFin),
            'rendimientoPorMateria' => $this->reporteRepo->rendimientoPorMateria($periodoId, $fechaInicio, $fechaFin),
            'estadisticasTareas' => $this->reporteRepo->estadisticasTareas($periodoId, $fechaInicio, $fechaFin),
            'visitasActivas' => $this->reporteRepo->visitasActivas($periodoId, $fechaInicio, $fechaFin),
            'paginasMasVisitadas' => $this->reporteRepo->paginasMasVisitadas($periodoId, $fechaInicio, $fechaFin),
            'ocupacionGrupos' => $this->reporteRepo->ocupacionGrupos($periodoId, $fechaInicio, $fechaFin),
        ]);
    }

    /**
     * Export all metrics to CSV/Excel format.
     */
    public function exportCsv(Request $request)
    {
        $periodoId = $request->input('periodo_academico_id');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $totalRecaudado = $this->cuotaRepo->totalRecaudado($periodoId, $fechaInicio, $fechaFin);
        $totalPorCobrar = $this->cuotaRepo->totalPorCobrar($periodoId, $fechaInicio, $fechaFin);
        $cuotasVencidasCount = $this->cuotaRepo->cuotasVencidas($periodoId, $fechaInicio, $fechaFin)->count();
        $totalMatriculasActivas = $this->matriculaRepo->totalMatriculasActivas($periodoId, $fechaInicio, $fechaFin);
        $indiceAprobacion = $this->reporteRepo->indiceAprobacion($periodoId, $fechaInicio, $fechaFin);
        $indiceReprobacion = $this->reporteRepo->indiceReprobacion($periodoId, $fechaInicio, $fechaFin);
        $ingresosMensuales = $this->reporteRepo->ingresosMensuales($periodoId, $fechaInicio, $fechaFin);
        $usoMetodosPago = $this->reporteRepo->usoMetodosPago($periodoId, $fechaInicio, $fechaFin);
        $rendimientoPorMateria = $this->reporteRepo->rendimientoPorMateria($periodoId, $fechaInicio, $fechaFin);
        $estadisticasTareas = $this->reporteRepo->estadisticasTareas($periodoId, $fechaInicio, $fechaFin);
        $ocupacionGrupos = $this->reporteRepo->ocupacionGrupos($periodoId, $fechaInicio, $fechaFin);
        $alumnosDeudores = $this->cuotaRepo->alumnosDeudores($periodoId, $fechaInicio, $fechaFin);

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=reporte_plataforma_" . now()->format('Ymd_His') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use (
            $periodoId, $fechaInicio, $fechaFin,
            $totalRecaudado, $totalPorCobrar, $cuotasVencidasCount, $totalMatriculasActivas,
            $indiceAprobacion, $indiceReprobacion, $ingresosMensuales, $usoMetodosPago,
            $rendimientoPorMateria, $estadisticasTareas, $ocupacionGrupos, $alumnosDeudores
        ) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel

            fputcsv($file, ['REPORTE DE METRICAS Y ESTADISTICAS']);
            fputcsv($file, ['Fecha de Generación:', now()->toDateTimeString()]);
            if ($periodoId) {
                $periodo = PeriodoAcademico::find($periodoId);
                fputcsv($file, ['Filtrado por Periodo Académico:', $periodo ? $periodo->nombre : 'N/A']);
            }
            if ($fechaInicio && $fechaFin) {
                fputcsv($file, ['Rango de Fechas:', $fechaInicio . ' a ' . $fechaFin]);
            }
            fputcsv($file, []);

            // 1. Resumen General
            fputcsv($file, ['--- RESUMEN GENERAL ---']);
            fputcsv($file, ['Métrica', 'Valor']);
            fputcsv($file, ['Total Recaudado (BOB)', $totalRecaudado]);
            fputcsv($file, ['Total por Cobrar (BOB)', $totalPorCobrar]);
            fputcsv($file, ['Cuotas Vencidas', $cuotasVencidasCount]);
            fputcsv($file, ['Matrículas Activas', $totalMatriculasActivas]);
            fputcsv($file, ['Índice de Aprobación (%)', $indiceAprobacion]);
            fputcsv($file, ['Índice de Reprobación (%)', $indiceReprobacion]);
            fputcsv($file, []);

            // 2. Ingresos Mensuales
            fputcsv($file, ['--- EVOLUCION DE INGRESOS MENSUALES ---']);
            fputcsv($file, ['Mes', 'Total Recaudado (BOB)']);
            foreach ($ingresosMensuales as $ingreso) {
                fputcsv($file, [$ingreso['mes'], $ingreso['total']]);
            }
            fputcsv($file, []);

            // 3. Uso de Métodos de Pago
            fputcsv($file, ['--- METODOS DE PAGO ---']);
            fputcsv($file, ['Método de Pago', 'Cantidad de Transacciones', 'Total Recaudado (BOB)']);
            foreach ($usoMetodosPago as $metodo) {
                fputcsv($file, [$metodo['metodo'], $metodo['cantidad'], $metodo['total']]);
            }
            fputcsv($file, []);

            // 4. Rendimiento por Materia
            fputcsv($file, ['--- RENDIMIENTO POR MATERIA ---']);
            fputcsv($file, ['Código', 'Materia', 'Total Alumnos', 'Aprobados', 'Reprobados', 'Retirados', 'Tasa Aprobación (%)', 'Tasa Reprobación (%)']);
            foreach ($rendimientoPorMateria as $materia) {
                fputcsv($file, [
                    $materia['codigo'],
                    $materia['nombre'],
                    $materia['total'],
                    $materia['aprobados'],
                    $materia['reprobados'],
                    $materia['retirados'],
                    $materia['tasa_aprobacion'],
                    $materia['tasa_reprobacion']
                ]);
            }
            fputcsv($file, []);

            // 5. Estadísticas de Tareas
            fputcsv($file, ['--- ESTADISTICAS DE TAREAS Y ENTREGAS ---']);
            fputcsv($file, ['Métrica de Tarea', 'Cantidad / Tasa']);
            fputcsv($file, ['Total Tareas Creadas', $estadisticasTareas['total_tareas']]);
            fputcsv($file, ['Total Entregas Realizadas', $estadisticasTareas['total_entregas']]);
            fputcsv($file, ['Entregas a Tiempo', $estadisticasTareas['entregas_a_tiempo']]);
            fputcsv($file, ['Entregas fuera de plazo (Tarde)', $estadisticasTareas['entregas_tarde']]);
            fputcsv($file, ['Entregas Pendientes', $estadisticasTareas['entregas_pendientes']]);
            fputcsv($file, ['Tasa de Entrega (%)', $estadisticasTareas['tasa_entrega']]);
            fputcsv($file, []);

            // 6. Ocupación de Grupos
            fputcsv($file, ['--- OCUPACION DE GRUPOS ---']);
            fputcsv($file, ['Código Grupo', 'Materia', 'Docente', 'Inscritos', 'Capacidad Máxima', 'Porcentaje de Ocupación (%)']);
            foreach ($ocupacionGrupos as $grupo) {
                fputcsv($file, [
                    $grupo['codigo'],
                    $grupo['materia'],
                    $grupo['docente'],
                    $grupo['inscritos'],
                    $grupo['capacidad'],
                    $grupo['porcentaje_ocupacion']
                ]);
            }
            fputcsv($file, []);

            // 7. Alumnos Deudores
            fputcsv($file, ['--- ALUMNOS CON DEUDAS VENCIDAS ---']);
            fputcsv($file, ['Nombre Estudiante', 'Email', 'Cuotas Vencidas', 'Total Deuda Pendiente (BOB)']);
            foreach ($alumnosDeudores as $deudor) {
                fputcsv($file, [
                    $deudor['nombre'],
                    $deudor['email'],
                    $deudor['cuotas_vencidas'],
                    $deudor['total_deuda']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
