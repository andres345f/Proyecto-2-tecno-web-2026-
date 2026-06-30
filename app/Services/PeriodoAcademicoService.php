<?php

namespace App\Services;

use App\Models\PeriodoAcademico;
use App\Repositories\PeriodoAcademicoRepository;
use App\Repositories\OfertaAcademicaRepository;
use Illuminate\Support\Collection;

class PeriodoAcademicoService
{
    protected PeriodoAcademicoRepository $periodoRepository;
    protected OfertaAcademicaRepository $ofertaRepository;

    public function __construct(
        PeriodoAcademicoRepository $periodoRepository,
        OfertaAcademicaRepository $ofertaRepository
    ) {
        $this->periodoRepository = $periodoRepository;
        $this->ofertaRepository = $ofertaRepository;
    }

    /**
     * Get filtered academic periods.
     */
    public function listarPeriodos(?int $ofertaAcademicaId): Collection
    {
        return $this->periodoRepository->obtenerFiltrados($ofertaAcademicaId);
    }

    /**
     * Get all offerings for dropdowns/filters.
     */
    public function obtenerOfertas(): Collection
    {
        return $this->ofertaRepository->obtenerTodasConMateriasCount();
    }

    /**
     * Create academic period.
     */
    public function crearPeriodo(array $data): PeriodoAcademico
    {
        return $this->periodoRepository->guardar($data);
    }

    /**
     * Update academic period.
     */
    public function actualizarPeriodo(PeriodoAcademico $periodo, array $data): bool
    {
        return $this->periodoRepository->actualizar($periodo, $data);
    }

    /**
     * Delete academic period.
     */
    public function eliminarPeriodo(PeriodoAcademico $periodo): bool
    {
        return $this->periodoRepository->eliminar($periodo);
    }

    public function cargarDetalles(PeriodoAcademico $periodo): PeriodoAcademico
    {
        $periodo->load(['ofertaAcademica']);

        $grupoPeriodos = \App\Models\GrupoPeriodo::where('periodo_academico_id', $periodo->id)
            ->with(['grupo.materia', 'docente', 'horarios.aula'])
            ->get();

        $periodo->setRelation('grupoPeriodos', $grupoPeriodos);

        $gruposMapped = $grupoPeriodos->map(function ($gp) {
            $grupo = $gp->grupo;
            if ($grupo) {
                $grupo->setRelation('docente', $gp->docente);
                $grupo->setRelation('horarios', $gp->horarios);
                $grupo->cupo_maximo = $gp->cupo_maximo;
                $grupo->grupo_periodo_id = $gp->id;
            }
            return $grupo;
        })->filter();

        $periodo->setRelation('grupos', $gruposMapped);

        return $periodo;
    }
}
