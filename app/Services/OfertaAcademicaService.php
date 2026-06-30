<?php

namespace App\Services;

use App\Models\OfertaAcademica;
use App\Repositories\OfertaAcademicaRepository;
use App\Repositories\MateriaRepository;
use Illuminate\Support\Collection;

class OfertaAcademicaService
{
    protected OfertaAcademicaRepository $ofertaRepository;
    protected MateriaRepository $materiaRepository;

    public function __construct(
        OfertaAcademicaRepository $ofertaRepository,
        MateriaRepository $materiaRepository
    ) {
        $this->ofertaRepository = $ofertaRepository;
        $this->materiaRepository = $materiaRepository;
    }

    /**
     * List all academic offerings.
     */
    public function listarOfertas(): Collection
    {
        return $this->ofertaRepository->obtenerTodasConMateriasCount();
    }

    /**
     * Create academic offering.
     */
    public function crearOferta(array $data): OfertaAcademica
    {
        return $this->ofertaRepository->guardar($data);
    }

    /**
     * Update academic offering.
     */
    public function actualizarOferta(OfertaAcademica $oferta, array $data): bool
    {
        return $this->ofertaRepository->actualizar($oferta, $data);
    }

    /**
     * Delete academic offering.
     */
    public function eliminarOferta(OfertaAcademica $oferta): bool
    {
        return $this->ofertaRepository->eliminar($oferta);
    }

    /**
     * Load structured details (malla curricular, periodos, prerrequisitos) of an offering.
     */
    public function cargarDetalles(OfertaAcademica $oferta): OfertaAcademica
    {
        $oferta->load([
            'mallaCurricular' => function ($query) {
                $query->orderBy('semestre_orden');
            },
            'mallaCurricular.materia',
            'mallaCurricular.prerrequisitos.materia',
            'periodosAcademicos' => function ($query) {
                $query->orderBy('fecha_inicio', 'desc');
            }
        ]);

        $materias = $oferta->mallaCurricular->map(function ($item) {
            if (!$item->materia) {
                return null;
            }
            $materia = $item->materia;
            $materia->pivot = [
                'semestre_orden' => $item->semestre_orden,
            ];
            $materia->prerrequisitos = $item->prerrequisitos->map(function ($prereq) {
                if (!$prereq->materia) {
                    return null;
                }
                return [
                    'id' => $prereq->materia->id,
                    'nombre' => $prereq->materia->nombre,
                    'codigo' => $prereq->materia->codigo,
                ];
            })->filter();
            return $materia;
        })->filter();

        unset($oferta->mallaCurricular);
        $oferta->setRelation('materias', $materias);

        return $oferta;
    }

    /**
     * Get all subjects ordered by name for dropdowns.
     */
    public function obtenerTodasLasMaterias(): Collection
    {
        return $this->materiaRepository->obtenerTodas();
    }
}
