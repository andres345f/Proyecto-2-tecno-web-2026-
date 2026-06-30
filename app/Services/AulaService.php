<?php

namespace App\Services;

use App\Models\Aula;
use App\Repositories\AulaRepository;
use Illuminate\Support\Collection;

class AulaService
{
    protected AulaRepository $aulaRepository;

    public function __construct(AulaRepository $aulaRepository)
    {
        $this->aulaRepository = $aulaRepository;
    }

    /**
     * Get filtered list of aulas.
     */
    public function listarAulas(?string $search = null, int $perPage = 10)
    {
        return $this->aulaRepository->obtenerFiltrados($search, $perPage);
    }

    /**
     * Create a new aula.
     */
    public function crearAula(array $data): Aula
    {
        return $this->aulaRepository->guardar($data);
    }

    /**
     * Update an existing aula.
     */
    public function actualizarAula(Aula $aula, array $data): bool
    {
        return $this->aulaRepository->actualizar($aula, $data);
    }

    /**
     * Delete an aula.
     */
    public function eliminarAula(Aula $aula): bool
    {
        return $this->aulaRepository->eliminar($aula);
    }

    /**
     * Load relations for showing details.
     */
    public function cargarDetalles(Aula $aula): Aula
    {
        return $aula->load(['horarios.grupoPeriodo.grupo.materia', 'horarios.grupoPeriodo.docente']);
    }
}
