<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;

class UserRepository
{
    public function obtenerFiltrados(?string $search = null, ?string $role = null, int $perPage = 10)
    {
        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where("is_{$role}", true);
        }

        return $query->orderBy('name')->paginate($perPage)->withQueryString();
    }

    /**
     * Store a new user in the database.
     */
    public function guardar(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update an existing user in the database.
     */
    public function actualizar(User $user, array $data): bool
    {
        return $user->update($data);
    }

    /**
     * Check if a user with the given email exists.
     */
    public function existeEmail(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    /**
     * Check if a user with the given student code exists.
     */
    public function existeCodigoEstudiante(string $codigo): bool
    {
        return User::where('codigo_estudiante', $codigo)->exists();
    }

    /**
     * Get user by student code.
     */
    public function obtenerPorCodigoEstudiante(string $codigo): ?User
    {
        return User::where('codigo_estudiante', $codigo)->first();
    }

    /**
     * Delete a user from the database.
     */
    public function eliminar(User $user): bool
    {
        return $user->delete();
    }
}
