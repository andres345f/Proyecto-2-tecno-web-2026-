<?php

namespace App\Services;

use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use App\Models\User;
use App\Repositories\MatriculaPeriodoRepository;
use Illuminate\Support\Collection;

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
        if (!$user->is_estudiante) {
            return true;
        }

        $matriculaCarrera = MatriculaCarrera::where('usuario_id', $user->id)
            ->where('estado', 'activo')
            ->first();

        if (!$matriculaCarrera) {
            return false;
        }

        $periodoInCurso = PeriodoAcademico::where('oferta_academica_id', $matriculaCarrera->oferta_academica_id)
            ->where('estado', '!=', 'terminado')
            ->first();

        if (!$periodoInCurso) {
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
            if ($matriculaCarreraId) {
                $matriculaCarrera = MatriculaCarrera::where('id', $matriculaCarreraId)
                    ->where('usuario_id', $user->id)
                    ->firstOrFail();
            } else {
                $matriculaCarrera = MatriculaCarrera::where('usuario_id', $user->id)
                    ->where('estado', 'activo')
                    ->firstOrFail();
            }

            if (!$this->determinarCanEnroll($user)) {
                abort(403, 'El período de inscripción no está activo o ha finalizado.');
            }
        } else {
            $matriculaCarrera = MatriculaCarrera::findOrFail($matriculaCarreraId);
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

            if (!$this->determinarCanEnroll($user)) {
                abort(403, 'El período de inscripción no está activo o ha finalizado.');
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
