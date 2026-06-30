<?php

namespace App\Repositories;

use App\Models\MatriculaCarrera;
use Illuminate\Support\Collection;

class MatriculaRepository
{
    /**
     * Total number of active career enrollments.
     */
    public function totalMatriculasActivas(): int
    {
        return MatriculaCarrera::where('estado', 'activo')->count();
    }

    /**
     * Count of matriculas grouped by OfertaAcademica.
     */
    public function matriculasPorOferta(): Collection
    {
        return MatriculaCarrera::where('estado', 'activo')
            ->selectRaw('oferta_academica_id, COUNT(*) as oferta_count')
            ->groupBy('oferta_academica_id')
            ->with('ofertaAcademica:id,nombre,codigo')
            ->get();
    }

    /**
     * Count of unique students enrolled per OfertaAcademica.
     */
    public function alumnosPorOferta(): Collection
    {
        return MatriculaCarrera::where('estado', 'activo')
            ->selectRaw('oferta_academica_id, COUNT(DISTINCT usuario_id) as alumnos_count')
            ->groupBy('oferta_academica_id')
            ->with('ofertaAcademica:id,nombre,codigo')
            ->get();
    }
}
