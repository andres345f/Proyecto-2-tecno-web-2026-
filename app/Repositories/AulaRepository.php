<?php

namespace App\Repositories;

use App\Models\Aula;
use Illuminate\Support\Collection;

class AulaRepository
{
    public function obtenerFiltrados(?string $search = null, int $perPage = 10)
    {
        $query = Aula::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(codigo) LIKE ?', ["%{$search}%"]);
            });
        }

        return $query->orderBy('nombre')->paginate($perPage)->withQueryString();
    }

    /**
     * Store a new aula in the database.
     */
    public function guardar(array $data): Aula
    {
        return Aula::create($data);
    }

    /**
     * Update an existing aula.
     */
    public function actualizar(Aula $aula, array $data): bool
    {
        return $aula->update($data);
    }

    /**
     * Delete an aula.
     */
    public function eliminar(Aula $aula): bool
    {
        return $aula->delete();
    }
}
