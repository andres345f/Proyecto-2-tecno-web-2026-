<?php

namespace App\Repositories;

use App\Models\Horario;
use Illuminate\Support\Collection;

class HorarioRepository
{
    /**
     * Get all schedules loaded with their aula and grupo.
     */
    public function obtenerTodosConRelaciones(): Collection
    {
        return Horario::with(['aula', 'grupoPeriodo.grupo.materia'])->orderBy('dia')->orderBy('hora_inicio')->get();
    }

    /**
     * Store a new schedule.
     */
    public function guardar(array $data): Horario
    {
        return Horario::create($data);
    }

    /**
     * Update an existing schedule.
     */
    public function actualizar(Horario $horario, array $data): bool
    {
        return $horario->update($data);
    }

    /**
     * Delete a schedule.
     */
    public function eliminar(Horario $horario): bool
    {
        return $horario->delete();
    }
}
