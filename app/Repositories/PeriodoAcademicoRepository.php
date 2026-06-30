<?php

namespace App\Repositories;

use App\Models\PeriodoAcademico;
use Illuminate\Support\Collection;

class PeriodoAcademicoRepository
{
    /**
     * Get filtered academic periods.
     */
    public function obtenerFiltrados(?int $ofertaAcademicaId): Collection
    {
        $query = PeriodoAcademico::with('ofertaAcademica');

        if ($ofertaAcademicaId) {
            $query->where('oferta_academica_id', $ofertaAcademicaId);
        }

        return $query->orderBy('nombre')->get();
    }

    /**
     * Store a new academic period.
     */
    public function guardar(array $data): PeriodoAcademico
    {
        return PeriodoAcademico::create($data);
    }

    /**
     * Update an academic period.
     */
    public function actualizar(PeriodoAcademico $periodo, array $data): bool
    {
        return $periodo->update($data);
    }

    /**
     * Delete an academic period.
     */
    public function eliminar(PeriodoAcademico $periodo): bool
    {
        return $periodo->delete();
    }
}
