<?php

namespace App\Services;

use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use App\Models\User;
use App\Repositories\MatriculaPeriodoRepository;

class MatriculaPeriodoService
{
    protected MatriculaPeriodoRepository $matriculaPeriodoRepository;

    protected GeneradorCuotasService $generadorCuotasService;

    public function __construct(
        MatriculaPeriodoRepository $matriculaPeriodoRepository,
        GeneradorCuotasService $generadorCuotasService
    ) {
        $this->matriculaPeriodoRepository = $matriculaPeriodoRepository;
        $this->generadorCuotasService = $generadorCuotasService;
    }

    /**
     * Get paginated and filtered matriculas periodo based on user role and query filters.
     */
    public function listarMatriculasPaginadas(User $user, int $perPage, ?int $matriculaCarreraId = null, ?string $search = null, ?string $estado = null)
    {
        $usuarioId = null;
        if ($user->is_estudiante) {
            $usuarioId = $user->id;
        }

        return $this->matriculaPeriodoRepository->obtenerPaginadasConFiltros(
            $usuarioId,
            $matriculaCarreraId,
            $perPage,
            $search,
            $estado
        );
    }

    /**
     * Determine if a student user is allowed to enroll in a new period.
     */
    public function determinarCanEnroll(User $user): bool
    {
        if (! $user->is_estudiante) {
            return true;
        }

        $matriculasCarrera = MatriculaCarrera::where('usuario_id', $user->id)
            ->where('estado', 'activo')
            ->get();

        foreach ($matriculasCarrera as $matriculaCarrera) {
            if ($this->canEnrollInCareer($matriculaCarrera)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if enrollment is active for a specific career matricula.
     */
    public function canEnrollInCareer(MatriculaCarrera $matriculaCarrera): bool
    {
        $periodoInCurso = PeriodoAcademico::where('oferta_academica_id', $matriculaCarrera->oferta_academica_id)
            ->where('estado', '!=', 'terminado')
            ->first();

        if (! $periodoInCurso) {
            return false;
        }

        $today = now()->toDateString();

        return $periodoInCurso->fecha_inicio_inscripcion
            && $periodoInCurso->fecha_fin_inscripcion
            && $today >= $periodoInCurso->fecha_inicio_inscripcion
            && $today <= $periodoInCurso->fecha_fin_inscripcion;
    }

    /**
     * Get required data to display the create enrollment form.
     */
    public function obtenerDatosCreacion(User $user, ?int $matriculaCarreraId): array
    {
        if ($user->is_estudiante) {
            $matriculasCarrera = MatriculaCarrera::with('ofertaAcademica')
                ->where('usuario_id', $user->id)
                ->where('estado', 'activo')
                ->get();

            if ($matriculaCarreraId) {
                $matriculaCarrera = MatriculaCarrera::where('id', $matriculaCarreraId)
                    ->where('usuario_id', $user->id)
                    ->firstOrFail();
            } else {
                // Try to find a career that has an active enrollment period first
                foreach ($matriculasCarrera as $mc) {
                    if ($this->canEnrollInCareer($mc)) {
                        $matriculaCarrera = $mc;
                        break;
                    }
                }
                // Fallback to first if none match
                if (! isset($matriculaCarrera)) {
                    $matriculaCarrera = $matriculasCarrera->first();
                }

                if (! $matriculaCarrera) {
                    abort(404, 'No tienes matrículas de carrera activas.');
                }
            }

            if (! $this->canEnrollInCareer($matriculaCarrera)) {
                abort(403, 'El período de inscripción para esta carrera no está activo o ha finalizado.');
            }
        } else {
            $matriculaCarrera = MatriculaCarrera::findOrFail($matriculaCarreraId);
            $matriculasCarrera = MatriculaCarrera::with('ofertaAcademica')
                ->where('usuario_id', $matriculaCarrera->usuario_id)
                ->where('estado', 'activo')
                ->get();
        }

        $periodos = PeriodoAcademico::with('ofertaAcademica')
            ->where('oferta_academica_id', $matriculaCarrera->oferta_academica_id)
            ->where('estado', '!=', 'terminado')
            ->orderBy('nombre')
            ->get();

        $planesQuery = PlanPago::where('oferta_academica_id', $matriculaCarrera->oferta_academica_id);

        if ($user->is_estudiante) {
            $planesQuery->where('tipo', '!=', 'especial');
        }

        $planes = $planesQuery->orderBy('nombre')->get();

        return [
            'matriculaCarrera' => $matriculaCarrera,
            'matriculasCarrera' => $matriculasCarrera,
            'periodos' => $periodos,
            'planes' => $planes,
        ];
    }

    /**
     * Create a new period enrollment and generate its cuotas.
     */
    public function crearMatriculaPeriodo(User $user, array $data): MatriculaPeriodo
    {
        if ($user->is_estudiante) {
            $matriculaCarrera = MatriculaCarrera::where('id', $data['matricula_carrera_id'])
                ->where('usuario_id', $user->id)
                ->firstOrFail();

            if (! $this->canEnrollInCareer($matriculaCarrera)) {
                abort(403, 'El período de inscripción para esta carrera no está activo o ha finalizado.');
            }
        }

        $matriculaPeriodo = $this->matriculaPeriodoRepository->guardar([
            'matricula_carrera_id' => $data['matricula_carrera_id'],
            'periodo_academico_id' => $data['periodo_academico_id'],
            'plan_pago_id' => $data['plan_pago_id'],
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        // Generate cuotas automatically
        $this->generadorCuotasService->generar($matriculaPeriodo);

        return $matriculaPeriodo;
    }

    /**
     * Load relations for showing details of an enrollment.
     */
    public function cargarDetalles(MatriculaPeriodo $matriculaPeriodo): MatriculaPeriodo
    {
        return $matriculaPeriodo->load([
            'periodoAcademico',
            'planPago',
            'matriculaCarrera.usuario',
            'matriculaCarrera.ofertaAcademica',
            'cuotas',
        ]);
    }

    /**
     * Update period enrollment state.
     */
    public function actualizarEstado(MatriculaPeriodo $matriculaPeriodo, string $estado): bool
    {
        return $this->matriculaPeriodoRepository->actualizar($matriculaPeriodo, ['estado' => $estado]);
    }

    /**
     * Delete a period enrollment.
     */
    public function eliminarMatriculaPeriodo(MatriculaPeriodo $matriculaPeriodo): bool
    {
        return $this->matriculaPeriodoRepository->eliminar($matriculaPeriodo);
    }
}
