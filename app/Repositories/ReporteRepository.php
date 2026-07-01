<?php

namespace App\Repositories;

use App\Models\MatriculaGrupo;
use App\Models\Pago;
use App\Models\Visita;
use App\Models\Tarea;
use App\Models\Entrega;
use App\Models\GrupoPeriodo;
use Illuminate\Support\Facades\DB;

class ReporteRepository
{
    /**
     * Percentage of matriculas_grupo with estado 'aprobado'.
     */
    public function indiceAprobacion($periodoId = null, $fechaInicio = null, $fechaFin = null): float
    {
        $query = MatriculaGrupo::query();
        if ($periodoId) {
            $query->whereHas('grupoPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        $total = $query->count();

        if ($total === 0) {
            return 0.0;
        }

        $aprobados = (clone $query)->where('estado', 'aprobado')->count();

        return round(($aprobados / $total) * 100, 2);
    }

    /**
     * Percentage of matriculas_grupo with estado 'reprobado'.
     */
    public function indiceReprobacion($periodoId = null, $fechaInicio = null, $fechaFin = null): float
    {
        $query = MatriculaGrupo::query();
        if ($periodoId) {
            $query->whereHas('grupoPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        $total = $query->count();

        if ($total === 0) {
            return 0.0;
        }

        $reprobados = (clone $query)->where('estado', 'reprobado')->count();

        return round(($reprobados / $total) * 100, 2);
    }

    /**
     * Combined financial stats.
     */
    public function resumenFinanciero($periodoId = null, $fechaInicio = null, $fechaFin = null): array
    {
        $cuotaRepo = new CuotaRepository;
        $matriculaRepo = new MatriculaRepository;

        return [
            'total_recaudado' => $cuotaRepo->totalRecaudado($periodoId, $fechaInicio, $fechaFin),
            'total_por_cobrar' => $cuotaRepo->totalPorCobrar($periodoId, $fechaInicio, $fechaFin),
            'total_matriculas_activas' => $matriculaRepo->totalMatriculasActivas($periodoId, $fechaInicio, $fechaFin),
            'indice_aprobacion' => $this->indiceAprobacion($periodoId, $fechaInicio, $fechaFin),
            'indice_reprobacion' => $this->indiceReprobacion($periodoId, $fechaInicio, $fechaFin),
        ];
    }

    /**
     * Monthly revenue evolution.
     */
    public function ingresosMensuales($periodoId = null, $fechaInicio = null, $fechaFin = null): array
    {
        $driver = DB::connection()->getDriverName();
        $dateExpr = $driver === 'sqlite' ? "strftime('%Y-%m', fecha_pago)" : "to_char(fecha_pago, 'YYYY-MM')";

        $query = Pago::where('estado', 'completado');
        if ($periodoId) {
            $query->whereHas('cuota.matriculaPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
        }

        $ingresos = $query->selectRaw("$dateExpr as mes, SUM(monto_pagado) as total")
            ->groupBy(DB::raw($dateExpr))
            ->orderBy('mes', 'asc')
            ->get();

        return $ingresos->map(function ($item) {
            return [
                'mes' => $item->mes,
                'total' => (float) $item->total,
            ];
        })->toArray();
    }

    /**
     * Usage of payment methods.
     */
    public function usoMetodosPago($periodoId = null, $fechaInicio = null, $fechaFin = null): array
    {
        $query = Pago::where('estado', 'completado');
        if ($periodoId) {
            $query->whereHas('cuota.matriculaPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
        }

        $metodos = $query->selectRaw('metodo_pago, COUNT(*) as cantidad, SUM(monto_pagado) as total')
            ->groupBy('metodo_pago')
            ->get();

        return $metodos->map(function ($item) {
            return [
                'metodo' => $item->metodo_pago,
                'cantidad' => (int) $item->cantidad,
                'total' => (float) $item->total,
            ];
        })->toArray();
    }

    /**
     * Performance breakdown by materia.
     */
    public function rendimientoPorMateria($periodoId = null, $fechaInicio = null, $fechaFin = null): array
    {
        $query = MatriculaGrupo::join('grupo_periodo', 'matriculas_grupo.grupo_periodo_id', '=', 'grupo_periodo.id')
            ->join('grupos', 'grupo_periodo.grupo_id', '=', 'grupos.id')
            ->join('materias', 'grupos.materia_id', '=', 'materias.id');

        if ($periodoId) {
            $query->where('grupo_periodo.periodo_academico_id', $periodoId);
        }

        $rendimiento = $query->selectRaw('
                materias.id as materia_id,
                materias.nombre as materia_nombre,
                materias.codigo as materia_codigo,
                COUNT(*) as total,
                COUNT(CASE WHEN matriculas_grupo.estado = \'aprobado\' THEN 1 END) as aprobados,
                COUNT(CASE WHEN matriculas_grupo.estado = \'reprobado\' THEN 1 END) as reprobados,
                COUNT(CASE WHEN matriculas_grupo.estado = \'retirado\' THEN 1 END) as retirados
            ')
            ->groupBy('materias.id', 'materias.nombre', 'materias.codigo')
            ->orderBy('total', 'desc')
            ->get();

        return $rendimiento->map(function ($item) {
            $total = (int) $item->total;
            return [
                'materia_id' => $item->materia_id,
                'nombre' => $item->materia_nombre,
                'codigo' => $item->materia_codigo,
                'total' => $total,
                'aprobados' => (int) $item->aprobados,
                'reprobados' => (int) $item->reprobados,
                'retirados' => (int) $item->retirados,
                'tasa_aprobacion' => $total > 0 ? round(($item->aprobados / $total) * 100, 2) : 0.0,
                'tasa_reprobacion' => $total > 0 ? round(($item->reprobados / $total) * 100, 2) : 0.0,
                'tasa_retirados' => $total > 0 ? round(($item->retirados / $total) * 100, 2) : 0.0,
            ];
        })->toArray();
    }

    /**
     * Statistics of task assignments and submissions.
     */
    public function estadisticasTareas($periodoId = null, $fechaInicio = null, $fechaFin = null): array
    {
        $tareasQuery = Tarea::query();
        $entregasQuery = Entrega::query();

        if ($periodoId) {
            $tareasQuery->whereHas('grupoPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
            $entregasQuery->whereHas('tarea.grupoPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        if ($fechaInicio && $fechaFin) {
            $tareasQuery->whereBetween('fecha_vencimiento', [$fechaInicio, $fechaFin]);
            $entregasQuery->whereBetween('fecha_entrega', [$fechaInicio, $fechaFin]);
        }

        $totalTareas = $tareasQuery->count();
        $totalEntregas = $entregasQuery->count();

        $entregasATiempo = (clone $entregasQuery)->join('tareas', 'entregas.tarea_id', '=', 'tareas.id')
            ->whereRaw('entregas.fecha_entrega <= tareas.fecha_vencimiento')
            ->count();

        $entregasTarde = (clone $entregasQuery)->join('tareas', 'entregas.tarea_id', '=', 'tareas.id')
            ->whereRaw('entregas.fecha_entrega > tareas.fecha_vencimiento')
            ->count();

        // Count expected submissions: sum of students in each group for all tasks
        $totalEsperado = 0;
        $tareasConGruposQuery = Tarea::query();
        if ($periodoId) {
            $tareasConGruposQuery->whereHas('grupoPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        if ($fechaInicio && $fechaFin) {
            $tareasConGruposQuery->whereBetween('fecha_vencimiento', [$fechaInicio, $fechaFin]);
        }

        $tareasConGrupos = $tareasConGruposQuery->withCount(['grupoPeriodo as alumnos_inscritos' => function ($query) {
            $query->join('matriculas_grupo', 'grupo_periodo.id', '=', 'matriculas_grupo.grupo_periodo_id');
        }])->get();

        foreach ($tareasConGrupos as $t) {
            $totalEsperado += $t->alumnos_inscritos;
        }

        $pendientes = max(0, $totalEsperado - $totalEntregas);

        return [
            'total_tareas' => $totalTareas,
            'total_entregas' => $totalEntregas,
            'entregas_a_tiempo' => $entregasATiempo,
            'entregas_tarde' => $entregasTarde,
            'entregas_pendientes' => $pendientes,
            'tasa_entrega' => $totalEsperado > 0 ? round(($totalEntregas / $totalEsperado) * 100, 2) : 0.0,
        ];
    }

    /**
     * Traffic and active users on the platform.
     */
    public function visitasActivas($periodoId = null, $fechaInicio = null, $fechaFin = null): array
    {
        // Daily Active Users (DAU) last 30 days
        $dauQuery = Visita::query();
        $mauQuery = Visita::query();
        $rolesQuery = DB::table('visitas')
            ->join('users', 'visitas.usuario_id', '=', 'users.id');

        if ($fechaInicio && $fechaFin) {
            $dauQuery->whereBetween('visitas.created_at', [$fechaInicio, $fechaFin]);
            $mauQuery->whereBetween('visitas.created_at', [$fechaInicio, $fechaFin]);
            $rolesQuery->whereBetween('visitas.created_at', [$fechaInicio, $fechaFin]);
        } else {
            $dauQuery->where('visitas.created_at', '>=', now()->subDays(30));
            $mauQuery->where('visitas.created_at', '>=', now()->subMonths(12));
        }

        $dauData = $dauQuery->selectRaw('DATE(created_at) as fecha, COUNT(DISTINCT usuario_id) as usuarios')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha', 'asc')
            ->get();

        $driver = DB::connection()->getDriverName();
        $dateExpr = $driver === 'sqlite' ? "strftime('%Y-%m', created_at)" : "to_char(created_at, 'YYYY-MM')";

        // Monthly Active Users (MAU) last 12 months
        $mauData = $mauQuery->selectRaw("$dateExpr as mes, COUNT(DISTINCT usuario_id) as usuarios")
            ->groupBy(DB::raw($dateExpr))
            ->orderBy('mes', 'asc')
            ->get();

        // breakdown by role
        $rolesBreakdown = $rolesQuery->selectRaw('
                SUM(CASE WHEN users.is_estudiante THEN 1 ELSE 0 END) as estudiante,
                SUM(CASE WHEN users.is_profesor THEN 1 ELSE 0 END) as profesor,
                SUM(CASE WHEN users.is_director THEN 1 ELSE 0 END) as director,
                SUM(CASE WHEN users.is_secretaria THEN 1 ELSE 0 END) as secretaria,
                SUM(CASE WHEN users.is_propietario THEN 1 ELSE 0 END) as propietario
            ')
            ->first();

        return [
            'dau' => $dauData->map(function ($item) {
                return ['fecha' => $item->fecha, 'usuarios' => (int) $item->usuarios];
            })->toArray(),
            'mau' => $mauData->map(function ($item) {
                return ['mes' => $item->mes, 'usuarios' => (int) $item->usuarios];
            })->toArray(),
            'roles' => [
                'Estudiantes' => (int) ($rolesBreakdown->estudiante ?? 0),
                'Profesores' => (int) ($rolesBreakdown->profesor ?? 0),
                'Directores' => (int) ($rolesBreakdown->director ?? 0),
                'Secretaria' => (int) ($rolesBreakdown->secretaria ?? 0),
                'Propietarios' => (int) ($rolesBreakdown->propietario ?? 0),
            ],
        ];
    }

    /**
     * Top visited pages.
     */
    public function paginasMasVisitadas($periodoId = null, $fechaInicio = null, $fechaFin = null): array
    {
        $query = Visita::query();
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        }
        $paginas = $query->selectRaw('url, COUNT(*) as visitas_count')
            ->groupBy('url')
            ->orderBy('visitas_count', 'desc')
            ->limit(10)
            ->get();

        return $paginas->map(function ($item) {
            return [
                'url' => $item->url,
                'cantidad' => (int) $item->visitas_count,
            ];
        })->toArray();
    }

    /**
     * Capacity occupancy of classroom groups.
     */
    public function ocupacionGrupos($periodoId = null, $fechaInicio = null, $fechaFin = null): array
    {
        $query = GrupoPeriodo::query();
        if ($periodoId) {
            $query->where('periodo_academico_id', $periodoId);
        }
        $grupos = $query->withCount('matriculasGrupo')
            ->with(['grupo.materia:id,nombre,codigo', 'docente:id,name'])
            ->get();

        return $grupos->map(function ($g) {
            $inscritos = (int) $g->matriculas_grupo_count;
            $capacidad = (int) ($g->cupo_maximo ?? 35);
            $porcentaje = $capacidad > 0 ? round(($inscritos / $capacidad) * 100, 2) : 0.0;
            
            return [
                'grupo_id' => $g->id,
                'codigo' => $g->grupo->codigo,
                'materia' => $g->grupo->materia->nombre ?? 'N/A',
                'materia_codigo' => $g->grupo->materia->codigo ?? 'N/A',
                'docente' => $g->docente->name ?? 'Sin Docente',
                'inscritos' => $inscritos,
                'capacidad' => $capacidad,
                'porcentaje_ocupacion' => $porcentaje,
            ];
        })->toArray();
    }
}
