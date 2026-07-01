<?php

namespace App\Repositories;

use App\Models\MatriculaCarrera;
use Illuminate\Support\Collection;

class MatriculaRepository
{
    /**
     * Total number of active career enrollments.
     */
    public function totalMatriculasActivas($periodoId = null, $fechaInicio = null, $fechaFin = null): int
    {
        $query = MatriculaCarrera::where('estado', 'activo');
        if ($periodoId) {
            $query->whereHas('matriculasPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_matricula', [$fechaInicio, $fechaFin]);
        }
        return $query->count();
    }

    /**
     * Count of matriculas grouped by OfertaAcademica.
     */
    public function matriculasPorOferta($periodoId = null, $fechaInicio = null, $fechaFin = null): Collection
    {
        $query = MatriculaCarrera::where('estado', 'activo');
        if ($periodoId) {
            $query->whereHas('matriculasPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_matricula', [$fechaInicio, $fechaFin]);
        }
        return $query->selectRaw('oferta_academica_id, COUNT(*) as oferta_count')
            ->groupBy('oferta_academica_id')
            ->with('ofertaAcademica:id,nombre,codigo')
            ->get();
    }

    /**
     * Count of unique students enrolled per OfertaAcademica.
     */
    public function alumnosPorOferta($periodoId = null, $fechaInicio = null, $fechaFin = null): Collection
    {
        $query = MatriculaCarrera::where('estado', 'activo');
        if ($periodoId) {
            $query->whereHas('matriculasPeriodo', function ($q) use ($periodoId) {
                $q->where('periodo_academico_id', $periodoId);
            });
        }
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_matricula', [$fechaInicio, $fechaFin]);
        }
        return $query->selectRaw('oferta_academica_id, COUNT(DISTINCT usuario_id) as alumnos_count')
            ->groupBy('oferta_academica_id')
            ->with('ofertaAcademica:id,nombre,codigo')
            ->get();
    }
}
