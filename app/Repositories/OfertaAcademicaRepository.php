<?php

namespace App\Repositories;

use App\Models\OfertaAcademica;
use Illuminate\Support\Collection;

class OfertaAcademicaRepository
{
    /**
     * Get all academic offerings with subjects count.
     */
    public function obtenerTodasConMateriasCount(): Collection
    {
        return OfertaAcademica::withCount('materias')->orderBy('nombre')->get();
    }

    /**
     * Store a new academic offering.
     */
    public function guardar(array $data): OfertaAcademica
    {
        return OfertaAcademica::create($data);
    }

    /**
     * Update an academic offering.
     */
    public function actualizar(OfertaAcademica $oferta, array $data): bool
    {
        return $oferta->update($data);
    }

    /**
     * Find an academic offering by its code.
     */
    public function obtenerPorCodigo(string $codigo): ?OfertaAcademica
    {
        return OfertaAcademica::where('codigo', $codigo)->first();
    }

    /**
     * Delete an academic offering.
     */
    public function eliminar(OfertaAcademica $oferta): bool
    {
        return $oferta->delete();
    }
}
