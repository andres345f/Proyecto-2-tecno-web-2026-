<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;

class UserService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get filtered users list.
     */
    public function listarUsuarios(?string $search = null, ?string $role = null, int $perPage = 10)
    {
        return $this->userRepository->obtenerFiltrados($search, $role, $perPage);
    }

    /**
     * Create a new user.
     */
    public function crearUsuario(array $data): User
    {
        if (!($data['is_estudiante'] ?? false)) {
            $data['codigo_estudiante'] = null;
        }

        $data['password'] = Hash::make($data['password']);

        return $this->userRepository->guardar($data);
    }

    /**
     * Update an existing user.
     */
    public function actualizarUsuario(User $user, array $data): bool
    {
        if (!($data['is_estudiante'] ?? false)) {
            $data['codigo_estudiante'] = null;
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->userRepository->actualizar($user, $data);
    }

    /**
     * Delete a user, preventing self-deletion.
     *
     * @throws \Exception
     */
    public function eliminarUsuario(User $user, int $currentUserId): bool
    {
        if ($user->id === $currentUserId) {
            throw new \Exception('No puedes eliminarte a ti mismo.');
        }

        return $this->userRepository->eliminar($user);
    }
}
