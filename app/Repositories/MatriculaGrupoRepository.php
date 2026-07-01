<?php

namespace App\Repositories;

use App\Models\GrupoPeriodo;
use App\Models\Horario;
use App\Models\MatriculaGrupo;
use App\Models\MatriculaPeriodo;
use Illuminate\Support\Collection;

class MatriculaGrupoRepository
{
    /**
     * Get group enrollments by student user ID.
     */
    public function obtenerMatriculasPorEstudiante(int $usuarioId): Collection
    {
        return MatriculaGrupo::whereHas('matriculaPeriodo.matriculaCarrera', function ($q) use ($usuarioId) {
            $q->where('usuario_id', $usuarioId);
        })
            ->with([
                'grupoPeriodo.grupo.materia',
                'grupoPeriodo.horarios.aula',
                'matriculaPeriodo.periodoAcademico',
                'grupoPeriodo' => function ($query) {
                    $query->withCount([
                        'matriculasGrupo as inscritos_count' => function ($sub) {
                            $sub->where('estado', '!=', 'retirado');
                        }
                    ]);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all groups with enrollment statistics for admin.
     */
    public function obtenerGruposConEstadisticas(): Collection
    {
        return GrupoPeriodo::with([
            'grupo.materia.ofertasAcademicas',
            'periodoAcademico',
            'horarios.aula',
            'matriculasGrupo' => function ($q) {
                $q->with('matriculaPeriodo.matriculaCarrera.usuario');
            }
        ])
            ->withCount([
                'matriculasGrupo as inscritos_count' => function ($sub) {
                    $sub->where('estado', '!=', 'retirado');
                }
            ])
            ->get();
    }

    public function obtenerGruposConEstadisticasPaginado(?string $search = null, ?string $periodo = null, ?string $rendimiento = null, int $perPage = 10)
    {
        $query = GrupoPeriodo::query()
            ->join('periodos_academicos', 'grupo_periodo.periodo_academico_id', '=', 'periodos_academicos.id')
            ->whereNull('periodos_academicos.deleted_at')
            ->select('grupo_periodo.*')
            ->orderByRaw("CASE WHEN periodos_academicos.estado = 'terminado' THEN 1 ELSE 0 END ASC")
            ->orderBy('periodos_academicos.fecha_inicio', 'desc')
            ->with([
                'grupo.materia.ofertasAcademicas',
                'periodoAcademico',
                'horarios.aula',
                'matriculasGrupo' => function ($q) {
                    $q->with('matriculaPeriodo.matriculaCarrera.usuario');
                }
            ])
            ->withCount([
                'matriculasGrupo as inscritos_count' => function ($sub) {
                    $sub->where('estado', '!=', 'retirado');
                }
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('grupo', function ($sub) use ($search) {
                    $sub->whereRaw('LOWER(codigo) LIKE ?', ["%{$search}%"])
                        ->orWhereHas('materia', function ($m) use ($search) {
                            $m->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"])
                              ->orWhereHas('ofertasAcademicas', function ($o) use ($search) {
                                  $o->whereRaw('LOWER(nombre) LIKE ?', ["%{$search}%"]);
                              });
                        });
                });
            });
        }

        if ($periodo) {
            $query->whereHas('periodoAcademico', function ($q) use ($periodo) {
                $q->where('nombre', $periodo);
            });
        }

        if ($rendimiento === 'con_notas') {
            $query->whereHas('matriculasGrupo', function ($q) {
                $q->whereNotNull('nota_final');
            });
        } elseif ($rendimiento === 'sin_notas') {
            $query->whereDoesntHave('matriculasGrupo', function ($q) {
                $q->whereNotNull('nota_final');
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get active matricula periodo for student.
     */
    public function obtenerMatriculaPeriodoPorEstudiante(int $usuarioId, ?int $matriculaPeriodoId = null): ?MatriculaPeriodo
    {
        $query = MatriculaPeriodo::whereHas('matriculaCarrera', function ($q) use ($usuarioId) {
            $q->where('usuario_id', $usuarioId);
        });

        if ($matriculaPeriodoId) {
            return $query->where('id', $matriculaPeriodoId)->first();
        }

        return $query->where('matriculas_periodo.estado', 'activo')
            ->join('periodos_academicos', 'matriculas_periodo.periodo_academico_id', '=', 'periodos_academicos.id')
            ->orderBy('periodos_academicos.fecha_inicio', 'desc')
            ->select('matriculas_periodo.*')
            ->first();
    }

    /**
     * Check if there is an open academic period for enrollment that the student is not yet registered in.
     */
    public function existePeriodoInscripcionDisponible(int $usuarioId): bool
    {
        $ofertasIds = \App\Models\MatriculaCarrera::where('usuario_id', $usuarioId)
            ->where('estado', 'activo')
            ->pluck('oferta_academica_id')
            ->toArray();

        if (empty($ofertasIds)) {
            return false;
        }

        $today = now()->toDateString();

        $periodosInscripcion = \App\Models\PeriodoAcademico::whereIn('oferta_academica_id', $ofertasIds)
            ->where('estado', '!=', 'terminado')
            ->whereNotNull('fecha_inicio_inscripcion')
            ->whereNotNull('fecha_fin_inscripcion')
            ->where('fecha_inicio_inscripcion', '<=', $today)
            ->where('fecha_fin_inscripcion', '>=', $today)
            ->get();

        if ($periodosInscripcion->isEmpty()) {
            return false;
        }

        foreach ($periodosInscripcion as $periodo) {
            $alreadyRegistered = \App\Models\MatriculaPeriodo::where('periodo_academico_id', $periodo->id)
                ->whereHas('matriculaCarrera', function ($q) use ($usuarioId) {
                    $q->where('usuario_id', $usuarioId);
                })
                ->exists();

            if (!$alreadyRegistered) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find matricula periodo by ID.
     */
    public function encontrarMatriculaPeriodo(int $id): ?MatriculaPeriodo
    {
        return MatriculaPeriodo::find($id);
    }

    /**
     * Find active matricula periodo.
     */
    public function obtenerMatriculaPeriodoActiva(): ?MatriculaPeriodo
    {
        return MatriculaPeriodo::where('estado', 'activo')->first();
    }

    /**
     * Get all active period enrollments.
     */
    public function obtenerMatriculasPeriodoActivas(bool $isEstudiante, int $usuarioId): Collection
    {
        if ($isEstudiante) {
            return MatriculaPeriodo::whereHas('matriculaCarrera', function ($q) use ($usuarioId) {
                $q->where('usuario_id', $usuarioId);
            })
                ->where('estado', 'activo')
                ->whereHas('periodoAcademico', function ($q) {
                    $q->where('estado', '!=', 'terminado');
                })
                ->with(['matriculaCarrera.ofertaAcademica', 'periodoAcademico'])
                ->get();
        }

        return MatriculaPeriodo::where('estado', 'activo')
            ->whereHas('periodoAcademico', function ($q) {
                $q->where('estado', '!=', 'terminado');
            })
            ->with(['matriculaCarrera.ofertaAcademica', 'periodoAcademico', 'matriculaCarrera.usuario'])
            ->get();
    }

    /**
     * Get IDs of materias already approved by the student.
     */
    public function obtenerMateriasAprobadasIds(int $usuarioId): array
    {
        return MatriculaGrupo::whereHas('matriculaPeriodo.matriculaCarrera', function ($q) use ($usuarioId) {
            $q->where('usuario_id', $usuarioId);
        })
            ->where('estado', '!=', 'retirado')
            ->where('nota_final', '>=', 51)
            ->join('grupo_periodo', 'matriculas_grupo.grupo_periodo_id', '=', 'grupo_periodo.id')
            ->join('grupos', 'grupo_periodo.grupo_id', '=', 'grupos.id')
            ->pluck('grupos.materia_id')
            ->toArray();
    }

    /**
     * Get IDs of materias where the student is already enrolled in this period.
     */
    public function obtenerMateriasInscritasPeriodoIds(int $matriculaPeriodoId): array
    {
        return MatriculaGrupo::where('matricula_periodo_id', $matriculaPeriodoId)
            ->where('estado', '!=', 'retirado')
            ->join('grupo_periodo', 'matriculas_grupo.grupo_periodo_id', '=', 'grupo_periodo.id')
            ->join('grupos', 'grupo_periodo.grupo_id', '=', 'grupos.id')
            ->pluck('grupos.materia_id')
            ->toArray();
    }

    /**
     * Filter groups for enrollment.
     */
    public function obtenerGruposParaInscripcion(int $ofertaId, int $periodoId, array $materiasAexcluir): Collection
    {
        return GrupoPeriodo::where('periodo_academico_id', $periodoId)
            ->whereHas('grupo.materia.ofertasAcademicas', function ($q) use ($ofertaId) {
                $q->where('ofertas_academicas.id', $ofertaId);
            })
            ->whereHas('grupo', function ($q) use ($materiasAexcluir) {
                $q->whereNotIn('materia_id', $materiasAexcluir);
            })
            ->with(['grupo.materia', 'docente', 'horarios.aula'])
            ->withCount([
                'matriculasGrupo as inscritos_count' => function ($q) {
                    $q->where('estado', '!=', 'retirado');
                }
            ])
            ->get();
    }

    /**
     * Get enrolled schedules for a period.
     */
    public function obtenerHorariosPorPeriodo(int $matriculaPeriodoId): Collection
    {
        $enrolledGrupoPeriodoIds = MatriculaGrupo::where('matricula_periodo_id', $matriculaPeriodoId)
            ->where('estado', '!=', 'retirado')
            ->pluck('grupo_periodo_id')
            ->toArray();

        return Horario::whereIn('grupo_periodo_id', $enrolledGrupoPeriodoIds)->with('grupoPeriodo.grupo')->get();
    }

    /**
     * Store new group enrollments.
     */
    public function guardarInscripciones(int $matriculaPeriodoId, array $grupoPeriodoIds): void
    {
        foreach ($grupoPeriodoIds as $gpId) {
            MatriculaGrupo::create([
                'matricula_periodo_id' => $matriculaPeriodoId,
                'grupo_periodo_id' => $gpId,
                'estado' => 'inscrito',
            ]);
        }
    }

    /**
     * Get detail of a group enrollment for student.
     */
    public function obtenerMatriculaGrupoDetalle(int $id): MatriculaGrupo
    {
        return MatriculaGrupo::with([
            'grupoPeriodo.grupo.materia',
            'grupoPeriodo.horarios.aula',
            'grupoPeriodo.docente',
            'matriculaPeriodo.periodoAcademico',
            'matriculaPeriodo.matriculaCarrera.usuario',
            'matriculaPeriodo.matriculaCarrera.ofertaAcademica',
        ])->findOrFail($id);
    }

    /**
     * Get detail of a group for admin.
     */
    public function obtenerGrupoDetalleAdmin(int $id): GrupoPeriodo
    {
        return GrupoPeriodo::with([
            'grupo.materia',
            'docente',
            'horarios.aula',
            'tareas',
            'matriculasGrupo' => function ($q) {
                $q->where('estado', '!=', 'retirado')
                    ->with('matriculaPeriodo.matriculaCarrera.usuario');
            }
        ])
            ->withCount([
                'matriculasGrupo as inscritos_count' => function ($sub) {
                    $sub->where('estado', '!=', 'retirado');
                }
            ])
            ->findOrFail($id);
    }

    /**
     * Find MatriculaGrupo by ID.
     */
    public function encontrarMatriculaGrupo(int $id): ?MatriculaGrupo
    {
        return MatriculaGrupo::find($id);
    }

    /**
     * Find MatriculaGrupo inside group for student.
     */
    public function obtenerMatriculaGrupoPorEstudiante(int $grupoPeriodoId, int $studentId): ?MatriculaGrupo
    {
        return MatriculaGrupo::where('grupo_periodo_id', $grupoPeriodoId)
            ->whereHas('matriculaPeriodo.matriculaCarrera', function ($q) use ($studentId) {
                $q->where('usuario_id', $studentId);
            })
            ->first();
    }

    /**
     * Find MatriculaGrupo inside group with relations.
     */
    public function obtenerMatriculasActivasPorGrupo(int $grupoPeriodoId): Collection
    {
        return MatriculaGrupo::where('grupo_periodo_id', $grupoPeriodoId)
            ->where('estado', '!=', 'retirado')
            ->with('matriculaPeriodo.matriculaCarrera.usuario')
            ->get();
    }

    /**
     * Find GrupoPeriodo by ID.
     */
    public function encontrarGrupoPeriodo(int $id): GrupoPeriodo
    {
        return GrupoPeriodo::findOrFail($id);
    }

    /**
     * Withdraw student from a group enrollment.
     */
    public function retirarMatriculaGrupo(MatriculaGrupo $matriculaGrupo): bool
    {
        return $matriculaGrupo->update(['estado' => 'retirado']);
    }
}
