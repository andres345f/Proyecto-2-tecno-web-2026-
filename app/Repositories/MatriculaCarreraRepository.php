<?php

namespace App\Repositories;

use App\Models\MatriculaCarrera;
use Illuminate\Support\Collection;

class MatriculaCarreraRepository
{
    /**
     * Get all enrollments with relations loaded.
     */
    public function obtenerTodasConRelaciones(): Collection
    {
        return MatriculaCarrera::with(['usuario', 'ofertaAcademica'])->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get all enrollments of a specific student.
     */
    public function obtenerPorUsuarioConRelaciones(int $usuarioId): Collection
    {
        return MatriculaCarrera::with(['usuario', 'ofertaAcademica'])
            ->where('usuario_id', $usuarioId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get paginated and filtered enrollments.
     */
    public function obtenerPaginadasConFiltros(?int $usuarioId, int $perPage, ?string $search = null, ?string $estado = null)
    {
        $query = MatriculaCarrera::with(['usuario', 'ofertaAcademica']);

        if ($usuarioId) {
            $query->where('usuario_id', $usuarioId);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('usuario', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('ofertaAcademica', function ($o) use ($search) {
                    $o->where('nombre', 'like', "%{$search}%")
                      ->orWhere('codigo', 'like', "%{$search}%");
                });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }

    /**
     * Store a new enrollment.
     */
    public function guardar(array $data): MatriculaCarrera
    {
        return MatriculaCarrera::create($data);
    }

    /**
     * Update enrollment state/data.
     */
    public function actualizar(MatriculaCarrera $matricula, array $data): bool
    {
        return $matricula->update($data);
    }

    /**
     * Delete an enrollment.
     */
    public function eliminar(MatriculaCarrera $matricula): bool
    {
        return $matricula->delete();
    }
}
