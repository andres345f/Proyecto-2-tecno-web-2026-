<?php

namespace App\Services;

use App\Models\Materia;
use App\Repositories\MateriaRepository;
use Illuminate\Support\Collection;

class MateriaService
{
    protected MateriaRepository $materiaRepository;

    public function __construct(MateriaRepository $materiaRepository)
    {
        $this->materiaRepository = $materiaRepository;
    }

    /**
     * Get filtered subjects list.
     */
    public function listarMaterias(?string $search, int $perPage = 10)
    {
        return $this->materiaRepository->obtenerFiltradasConPrerrequisitosCount($search, $perPage);
    }

    /**
     * Get all subjects ordered by name.
     */
    public function obtenerTodas(): Collection
    {
        return $this->materiaRepository->obtenerTodas();
    }

    /**
     * Create subject.
     */
    public function crearMateria(array $data): Materia
    {
        return $this->materiaRepository->guardar($data);
    }

    /**
     * Update subject.
     */
    public function actualizarMateria(Materia $materia, array $data): bool
    {
        return $this->materiaRepository->actualizar($materia, $data);
    }

    /**
     * Delete subject.
     */
    public function eliminarMateria(Materia $materia): bool
    {
        return $this->materiaRepository->eliminar($materia);
    }

    /**
     * Load prerequisites and dependent subjects.
     */
    public function cargarRelacionesYPrerrequisitos(Materia $materia): Materia
    {
        $prerrequisitos = Materia::whereHas('mallaCurricular', function ($q) use ($materia) {
            $q->whereIn('id', function ($sub) use ($materia) {
                $sub->select('prerequisito_malla_id')
                    ->from('materia_prerequisito')
                    ->whereIn('malla_curricular_id', function ($sub2) use ($materia) {
                        $sub2->select('id')
                            ->from('malla_curricular')
                            ->where('materia_id', $materia->id);
                    });
            });
        })->distinct()->get();

        $esPrerequisitoDe = Materia::whereHas('mallaCurricular', function ($q) use ($materia) {
            $q->whereIn('id', function ($sub) use ($materia) {
                $sub->select('malla_curricular_id')
                    ->from('materia_prerequisito')
                    ->whereIn('prerequisito_malla_id', function ($sub2) use ($materia) {
                        $sub2->select('id')
                            ->from('malla_curricular')
                            ->where('materia_id', $materia->id);
                    });
            });
        })->distinct()->get();

        $materia->setRelation('prerrequisitos', $prerrequisitos);
        $materia->setRelation('esPrerequisitoDe', $esPrerequisitoDe);

        return $materia;
    }
}
