<?php

namespace App\Repositories;

use App\Models\Materia;
use Illuminate\Support\Collection;

class MateriaRepository
{
    /**
     * Get all subjects ordered by name.
     */
    public function obtenerTodas(): Collection
    {
        return Materia::orderBy('nombre')->get();
    }

    /**
     * Get subjects filtered by search query.
     */
    public function obtenerFiltradas(?string $search, int $perPage = 10)
    {
        $query = Materia::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(codigo) LIKE ?', ["%{$search}%"]);
            });
        }

        return $query->orderBy('nombre')->paginate($perPage)->withQueryString();
    }

    /**
     * Store a new subject.
     */
    public function guardar(array $data): Materia
    {
        return Materia::create($data);
    }

    /**
     * Update a subject.
     */
    public function actualizar(Materia $materia, array $data): bool
    {
        return $materia->update($data);
    }

    /**
     * Delete a subject.
     */
    public function eliminar(Materia $materia): bool
    {
        return $materia->delete();
    }
}
