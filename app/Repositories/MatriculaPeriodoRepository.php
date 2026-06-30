<?php

namespace App\Repositories;

use App\Models\MatriculaPeriodo;

class MatriculaPeriodoRepository
{
    /**
     * Get paginated and filtered matriculas periodo.
     */
    public function obtenerPaginadasConFiltros(?int $usuarioId, ?int $matriculaCarreraId, int $perPage, ?string $search = null, ?string $estado = null)
    {
        $query = MatriculaPeriodo::with(['periodoAcademico', 'planPago', 'matriculaCarrera.usuario']);

        if ($usuarioId) {
            $query->whereHas('matriculaCarrera', function ($q) use ($usuarioId) {
                $q->where('usuario_id', $usuarioId);
            });
        } elseif ($matriculaCarreraId) {
            $query->where('matricula_carrera_id', $matriculaCarreraId);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('matriculaCarrera.usuario', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('periodoAcademico', function ($p) use ($search) {
                    $p->where('nombre', 'like', "%{$search}%");
                })->orWhereHas('planPago', function ($pl) use ($search) {
                    $pl->where('nombre', 'like', "%{$search}%");
                });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }

    /**
     * Store a new matricula periodo.
     */
    public function guardar(array $data): MatriculaPeriodo
    {
        return MatriculaPeriodo::create($data);
    }

    /**
     * Update an existing matricula periodo.
     */
    public function actualizar(MatriculaPeriodo $matriculaPeriodo, array $data): bool
    {
        return $matriculaPeriodo->update($data);
    }

    /**
     * Delete a matricula periodo.
     */
    public function eliminar(MatriculaPeriodo $matriculaPeriodo): bool
    {
        return $matriculaPeriodo->delete();
    }
}
