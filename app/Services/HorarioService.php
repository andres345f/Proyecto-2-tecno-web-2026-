<?php

namespace App\Services;

use App\Models\Horario;
use App\Repositories\HorarioRepository;
use Illuminate\Support\Collection;

class HorarioService
{
    protected HorarioRepository $horarioRepository;

    public function __construct(HorarioRepository $horarioRepository)
    {
        $this->horarioRepository = $horarioRepository;
    }

    /**
     * Get all schedules.
     */
    public function listarHorarios(): Collection
    {
        return $this->horarioRepository->obtenerTodosConRelaciones();
    }

    /**
     * Create a new schedule.
     */
    public function crearHorario(array $data): Horario
    {
        return $this->horarioRepository->guardar($data);
    }

    /**
     * Update an existing schedule.
     */
    public function actualizarHorario(Horario $horario, array $data): bool
    {
        return $this->horarioRepository->actualizar($horario, $data);
    }

    /**
     * Delete a schedule.
     */
    public function eliminarHorario(Horario $horario): bool
    {
        return $this->horarioRepository->eliminar($horario);
    }

    /**
     * Load details/relations for a schedule.
     */
    public function cargarDetalles(Horario $horario): Horario
    {
        return $horario->load(['aula', 'grupoPeriodo.grupo.materia']);
    }
}
