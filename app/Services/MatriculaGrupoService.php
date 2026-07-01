<?php

namespace App\Services;

use App\Models\User;
use App\Models\MatriculaGrupo;
use App\Repositories\MatriculaGrupoRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class MatriculaGrupoService
{
    protected MatriculaGrupoRepository $repository;
    protected UserRepository $userRepository;
    protected ValidadorPrerrequisitos $validadorPrerrequisitos;

    public function __construct(
        MatriculaGrupoRepository $repository,
        UserRepository $userRepository,
        ValidadorPrerrequisitos $validadorPrerrequisitos
    ) {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
        $this->validadorPrerrequisitos = $validadorPrerrequisitos;
    }

    /**
     * Get index data based on user role.
     */
    public function obtenerDatosIndex(User $user, ?string $search = null, ?string $periodo = null, ?string $rendimiento = null): array
    {
        if ($user->is_estudiante) {
            $canEnroll = $this->verificarPermisoInscripcion($user);
            $canEnrollPeriod = $this->verificarDisponibilidadPeriodo($user);
            $matriculas = $this->repository->obtenerMatriculasPorEstudiante($user->id)->map(function ($m) {
                return [
                    'id' => $m->id,
                    'nota_final' => $m->nota_final,
                    'estado' => $m->estado,
                    'grupo' => [
                        'id' => $m->grupoPeriodo->id,
                        'codigo' => $m->grupoPeriodo->grupo->codigo,
                        'materia' => [
                            'id' => $m->grupoPeriodo->grupo->materia->id,
                            'nombre' => $m->grupoPeriodo->grupo->materia->nombre,
                            'codigo' => $m->grupoPeriodo->grupo->materia->codigo,
                        ],
                        'horarios' => $m->grupoPeriodo->horarios->map(function ($h) {
                            return [
                                'dia' => $h->dia,
                                'hora_inicio' => $h->hora_inicio,
                                'hora_fin' => $h->hora_fin,
                                'aula' => [
                                    'nombre' => $h->aula->nombre ?? 'Sin aula',
                                ]
                            ];
                        })->toArray(),
                    ],
                    'matricula_periodo' => [
                        'periodo_academico' => [
                            'nombre' => $m->matriculaPeriodo->periodoAcademico->nombre,
                        ],
                        'matricula_carrera' => [
                            'usuario' => [
                                'name' => $m->matriculaPeriodo->matriculaCarrera->usuario->name,
                            ],
                            'oferta_academica' => [
                                'nombre' => $m->matriculaPeriodo->matriculaCarrera->ofertaAcademica->nombre,
                            ]
                        ]
                    ]
                ];
            });

            return [
                'matriculas' => $matriculas,
                'canEnroll' => $canEnroll,
                'canEnrollPeriod' => $canEnrollPeriod,
            ];
        }

        $paginator = $this->repository->obtenerGruposConEstadisticasPaginado($search, $periodo, $rendimiento);

        $paginator->through(function ($grupoPeriodo) {
            $periodoNombre = $grupoPeriodo->periodoAcademico ? $grupoPeriodo->periodoAcademico->nombre : 'Sin período';

            $carreraNombre = $grupoPeriodo->grupo->materia && $grupoPeriodo->grupo->materia->ofertasAcademicas && $grupoPeriodo->grupo->materia->ofertasAcademicas->isNotEmpty()
                ? $grupoPeriodo->grupo->materia->ofertasAcademicas->pluck('nombre')->implode(', ')
                : 'Sin carrera';

            $aprobadosCount = $grupoPeriodo->matriculasGrupo->filter(function ($m) {
                return $m->estado === 'aprobado' || ($m->nota_final !== null && $m->nota_final >= 51);
            })->count();

            $reprobadosCount = $grupoPeriodo->matriculasGrupo->filter(function ($m) {
                return $m->estado === 'reprobado' || ($m->nota_final !== null && $m->nota_final < 51);
            })->count();

            $tieneNotas = $grupoPeriodo->matriculasGrupo->contains(function ($m) {
                return $m->nota_final !== null;
            });

            return [
                'id' => $grupoPeriodo->id,
                'codigo' => $grupoPeriodo->grupo->codigo,
                'materia' => $grupoPeriodo->grupo->materia,
                'horarios' => $grupoPeriodo->horarios,
                'cupo_maximo' => $grupoPeriodo->cupo_maximo,
                'inscritos_count' => $grupoPeriodo->inscritos_count,
                'periodo_nombre' => $periodoNombre,
                'carrera_nombre' => $carreraNombre,
                'aprobados_count' => $aprobadosCount,
                'reprobados_count' => $reprobadosCount,
                'tiene_notas' => $tieneNotas,
            ];
        });

        $periodosDisponibles = \App\Models\PeriodoAcademico::orderBy('fecha_inicio', 'desc')->pluck('nombre')->unique()->values()->toArray();

        return [
            'grupos' => $paginator,
            'periodosDisponibles' => $periodosDisponibles,
        ];
    }

    /**
     * Check if student user is allowed to enroll.
     */
    public function verificarPermisoInscripcion(User $user): bool
    {
        $matriculaPeriodo = $this->repository->obtenerMatriculaPeriodoPorEstudiante($user->id);
        if (!$matriculaPeriodo) {
            return false;
        }

        $periodo = $matriculaPeriodo->periodoAcademico;
        if (!$periodo || $periodo->estado === 'terminado') {
            return false;
        }

        $today = now()->toDateString();
        return $periodo->fecha_inicio_inscripcion
            && $periodo->fecha_fin_inscripcion
            && $today >= $periodo->fecha_inicio_inscripcion
            && $today <= $periodo->fecha_fin_inscripcion;
    }

    /**
     * Check if there is an open enrollment period the student can still join.
     */
    public function verificarDisponibilidadPeriodo(User $user): bool
    {
        return $this->repository->existePeriodoInscripcionDisponible($user->id);
    }

    /**
     * Get data for create group enrollment page.
     */
    public function obtenerDatosCrear(User $user, ?int $matriculaPeriodoId): array
    {
        $matriculaPeriodo = null;

        if ($user->is_estudiante) {
            $matriculaPeriodo = $this->repository->obtenerMatriculaPeriodoPorEstudiante($user->id, $matriculaPeriodoId);

            if (!$matriculaPeriodo) {
                abort(403, 'Debes estar inscrito en un período académico activo.');
            }

            $periodoInCurso = $matriculaPeriodo->periodoAcademico;
            if ($periodoInCurso->estado === 'terminado') {
                abort(403, 'El período académico ha terminado.');
            }

            $today = now()->toDateString();
            $canEnroll = $periodoInCurso->fecha_inicio_inscripcion
                && $periodoInCurso->fecha_fin_inscripcion
                && $today >= $periodoInCurso->fecha_inicio_inscripcion
                && $today <= $periodoInCurso->fecha_fin_inscripcion;

            if (!$canEnroll) {
                abort(403, 'El período de inscripción no está activo o ha finalizado.');
            }
        } else {
            $matriculaPeriodo = $matriculaPeriodoId
                ? $this->repository->encontrarMatriculaPeriodo($matriculaPeriodoId)
                : $this->repository->obtenerMatriculaPeriodoActiva();
        }

        $matriculasPeriodo = $this->repository->obtenerMatriculasPeriodoActivas($user->is_estudiante, $user->id);
        $grupos = collect();

        if ($matriculaPeriodo) {
            $ofertaId = $matriculaPeriodo->matriculaCarrera->oferta_academica_id;
            $usuarioId = $matriculaPeriodo->matriculaCarrera->usuario_id;
            $periodoId = $matriculaPeriodo->periodo_academico_id;

            $materiasAprobadasIds = $this->repository->obtenerMateriasAprobadasIds($usuarioId);
            $materiasInscritasPeriodoIds = $this->repository->obtenerMateriasInscritasPeriodoIds($matriculaPeriodo->id);
            $materiasAexcluir = array_unique(array_merge($materiasAprobadasIds, $materiasInscritasPeriodoIds));

            $rawGrupos = $this->repository->obtenerGruposParaInscripcion($ofertaId, $periodoId, $materiasAexcluir);
            $existingHorarios = $this->repository->obtenerHorariosPorPeriodo($matriculaPeriodo->id);

            $grupos = $rawGrupos->map(function ($grupoPeriodo) use ($matriculaPeriodo, $existingHorarios) {
                $cumple = true;
                $mensaje = '';

                try {
                    $this->validadorPrerrequisitos->validar($matriculaPeriodo, $grupoPeriodo);
                } catch (ValidationException $e) {
                    $cumple = false;
                    $errors = $e->validator->errors();
                    $mensaje = $errors->first('grupo_id') ?: $errors->first('grupo_ids') ?: 'No cumple prerrequisitos.';
                }

                if ($cumple) {
                    foreach ($grupoPeriodo->horarios as $hNew) {
                        foreach ($existingHorarios as $hExisting) {
                            if (strtolower($hNew->dia) === strtolower($hExisting->dia)) {
                                $start1 = $hNew->hora_inicio;
                                $end1 = $hNew->hora_fin;
                                $start2 = $hExisting->hora_inicio;
                                $end2 = $hExisting->hora_fin;

                                if ($start1 < $end2 && $start2 < $end1) {
                                    $cumple = false;
                                    $mensaje = "Conflicto de horario: se cruza con el grupo inscrito {$hExisting->grupoPeriodo->grupo->codigo} ({$hExisting->dia} {$start2}-{$end2}).";
                                    break 2;
                                }
                            }
                        }
                    }
                }

                return [
                    'id' => $grupoPeriodo->id,
                    'codigo' => $grupoPeriodo->grupo->codigo,
                    'cupo_maximo' => $grupoPeriodo->cupo_maximo,
                    'materia_id' => $grupoPeriodo->grupo->materia_id,
                    'docente_id' => $grupoPeriodo->docente_id,
                    'materia' => $grupoPeriodo->grupo->materia,
                    'docente' => [
                        'id' => $grupoPeriodo->docente->id ?? null,
                        'name' => $grupoPeriodo->docente->name ?? 'No asignado',
                    ],
                    'horarios' => $grupoPeriodo->horarios->map(function ($h) {
                        return [
                            'id' => $h->id,
                            'dia_semana' => $h->dia,
                            'dia' => $h->dia,
                            'hora_inicio' => $h->hora_inicio,
                            'hora_fin' => $h->hora_fin,
                            'aula' => $h->aula ? [
                                'id' => $h->aula->id,
                                'nombre' => $h->aula->nombre,
                                'codigo' => $h->aula->codigo,
                            ] : null,
                        ];
                    })->toArray(),
                    'cumple_prerrequisitos' => $cumple,
                    'prerrequisito_mensaje' => $mensaje,
                    'inscritos_count' => $grupoPeriodo->inscritos_count,
                ];
            });
        }

        return [
            'matriculasPeriodo' => $matriculasPeriodo,
            'grupos' => $grupos,
            'matriculaPeriodoId' => $matriculaPeriodo ? $matriculaPeriodo->id : null,
        ];
    }

    /**
     * Store new enrollments with validation checks.
     */
    public function inscribirEstudianteAGrupos(User $user, int $matriculaPeriodoId, array $grupoIds): void
    {
        $matriculaPeriodo = $this->repository->encontrarMatriculaPeriodo($matriculaPeriodoId);
        if (!$matriculaPeriodo) {
            abort(404, 'Matrícula de período no encontrada.');
        }

        if ($user->is_estudiante) {
            if ($matriculaPeriodo->matriculaCarrera->usuario_id !== $user->id) {
                abort(403, 'Unauthorized.');
            }
            $periodoInCurso = $matriculaPeriodo->periodoAcademico;
            if ($periodoInCurso->estado === 'terminado') {
                abort(403, 'El período académico ha terminado.');
            }
            $today = now()->toDateString();
            $canEnroll = $periodoInCurso->fecha_inicio_inscripcion
                && $periodoInCurso->fecha_fin_inscripcion
                && $today >= $periodoInCurso->fecha_inicio_inscripcion
                && $today <= $periodoInCurso->fecha_fin_inscripcion;

            if (!$canEnroll) {
                abort(403, 'El período de inscripción no está activo o ha finalizado.');
            }
        }

        // Fetch already enrolled schedules for this period (excluding retired ones)
        $existingHorarios = $this->repository->obtenerHorariosPorPeriodo($matriculaPeriodo->id);

        // Populate checking list
        $allHorariosToCheck = [];
        foreach ($existingHorarios as $h) {
            $allHorariosToCheck[] = [
                'dia' => $h->dia,
                'hora_inicio' => $h->hora_inicio,
                'hora_fin' => $h->hora_fin,
                'label' => "el grupo ya inscrito " . ($h->grupoPeriodo->grupo->codigo ?? ''),
            ];
        }

        // Fetch new group periods with their schedules
        $newGrupoPeriodos = \App\Models\GrupoPeriodo::with('horarios', 'grupo.materia')->whereIn('id', $grupoIds)->get();

        // Perform schedule conflict checks
        foreach ($newGrupoPeriodos as $gp) {
            foreach ($gp->horarios as $hNew) {
                foreach ($allHorariosToCheck as $existing) {
                    if (strtolower($hNew->dia) === strtolower($existing['dia'])) {
                        $start1 = $hNew->hora_inicio;
                        $end1 = $hNew->hora_fin;
                        $start2 = $existing['hora_inicio'];
                        $end2 = $existing['hora_fin'];

                        if ($start1 < $end2 && $start2 < $end1) {
                            throw ValidationException::withMessages([
                                'grupo_ids' => ["Conflicto de horario: El grupo {$gp->grupo->codigo} ({$gp->grupo->materia->nombre}) se cruza con {$existing['label']} ({$existing['dia']} {$start2}-{$end2})."]
                            ]);
                        }
                    }
                }
                // Add to checking list for internal conflicts among new selections
                $allHorariosToCheck[] = [
                    'dia' => $hNew->dia,
                    'hora_inicio' => $hNew->hora_inicio,
                    'hora_fin' => $hNew->hora_fin,
                    'label' => "el grupo seleccionado " . $gp->grupo->codigo,
                ];
            }
        }

        \DB::transaction(function () use ($grupoIds, $matriculaPeriodo) {
            foreach ($grupoIds as $gpId) {
                $gp = \App\Models\GrupoPeriodo::findOrFail($gpId);
                $this->validadorPrerrequisitos->validar($matriculaPeriodo, $gp);

                MatriculaGrupo::create([
                    'matricula_periodo_id' => $matriculaPeriodo->id,
                    'grupo_periodo_id' => $gp->id,
                    'estado' => 'inscrito',
                ]);
            }
        });
    }

    /**
     * Get detail data of enrollment based on role.
     */
    public function obtenerDetalles(User $user, int $id): array
    {
        if ($user->is_estudiante) {
            $m = $this->repository->obtenerMatriculaGrupoDetalle($id);
            return [
                'matricula' => [
                    'id' => $m->id,
                    'nota_final' => $m->nota_final,
                    'estado' => $m->estado,
                    'created_at' => $m->created_at->toIso8601String(),
                    'grupo' => [
                        'id' => $m->grupoPeriodo->id,
                        'codigo' => $m->grupoPeriodo->grupo->codigo,
                        'materia' => [
                            'nombre' => $m->grupoPeriodo->grupo->materia->nombre,
                            'codigo' => $m->grupoPeriodo->grupo->materia->codigo,
                            'descripcion' => $m->grupoPeriodo->grupo->materia->descripcion,
                        ],
                        'docente' => [
                            'name' => $m->grupoPeriodo->docente->name ?? 'Sin docente',
                            'email' => $m->grupoPeriodo->docente->email ?? '',
                        ],
                        'horarios' => $m->grupoPeriodo->horarios->map(function ($h) {
                            return [
                                'dia' => $h->dia,
                                'hora_inicio' => $h->hora_inicio,
                                'hora_fin' => $h->hora_fin,
                                'aula' => [
                                    'nombre' => $h->aula->nombre ?? 'Sin aula',
                                    'codigo' => $h->aula->codigo ?? '',
                                    'capacidad' => $h->aula->capacidad ?? 0,
                                ]
                            ];
                        })->toArray()
                    ],
                    'matricula_periodo' => [
                        'periodo_academico' => [
                            'nombre' => $m->matriculaPeriodo->periodoAcademico->nombre,
                            'fecha_inicio' => $m->matriculaPeriodo->periodoAcademico->fecha_inicio->toDateString(),
                            'fecha_fin' => $m->matriculaPeriodo->periodoAcademico->fecha_fin->toDateString(),
                        ],
                        'matricula_carrera' => [
                            'usuario' => [
                                'name' => $m->matriculaPeriodo->matriculaCarrera->usuario->name,
                                'email' => $m->matriculaPeriodo->matriculaCarrera->usuario->email,
                            ],
                            'oferta_academica' => [
                                'nombre' => $m->matriculaPeriodo->matriculaCarrera->ofertaAcademica->nombre,
                                'codigo' => $m->matriculaPeriodo->matriculaCarrera->ofertaAcademica->codigo,
                            ]
                        ]
                    ]
                ]
            ];
        }

        $gp = $this->repository->obtenerGrupoDetalleAdmin($id);
        return [
            'grupo' => [
                'id' => $gp->id,
                'codigo' => $gp->grupo->codigo,
                'materia' => [
                    'nombre' => $gp->grupo->materia->nombre,
                    'codigo' => $gp->grupo->materia->codigo,
                    'descripcion' => $gp->grupo->materia->descripcion,
                ],
                'docente' => [
                    'name' => $gp->docente->name ?? 'No asignado',
                    'email' => $gp->docente->email ?? '',
                ],
                'inscritos_count' => $gp->inscritos_count,
                'cupo_maximo' => $gp->cupo_maximo,
                'horarios' => $gp->horarios->map(function ($h) {
                    return [
                        'dia' => $h->dia,
                        'hora_inicio' => $h->hora_inicio,
                        'hora_fin' => $h->hora_fin,
                        'aula' => [
                            'nombre' => $h->aula->nombre ?? 'Sin aula',
                            'codigo' => $h->aula->codigo ?? '',
                            'capacidad' => $h->aula->capacidad ?? 0,
                        ]
                    ];
                })->toArray(),
                'tareas' => $gp->tareas->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'titulo' => $t->titulo,
                        'descripcion' => $t->descripcion,
                        'fecha_limite' => $t->fecha_vencimiento->toIso8601String(),
                        'puntos' => $t->puntaje_maximo,
                    ];
                })->toArray(),
                'matriculas_grupo' => $gp->matriculasGrupo->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'nota_final' => $m->nota_final,
                        'estado' => $m->estado,
                        'matricula_periodo' => [
                            'matricula_carrera' => [
                                'usuario' => [
                                    'name' => $m->matriculaPeriodo->matriculaCarrera->usuario->name,
                                    'email' => $m->matriculaPeriodo->matriculaCarrera->usuario->email,
                                    'codigo_estudiante' => $m->matriculaPeriodo->matriculaCarrera->usuario->codigo_estudiante,
                                ]
                            ]
                        ]
                    ];
                })->toArray()
            ]
        ];
    }

    /**
     * Generate grades template CSV structure.
     */
    public function generarPlantillaNotas(int $grupoId): array
    {
        $grupo = $this->repository->encontrarGrupoPeriodo($grupoId);
        $matriculas = $this->repository->obtenerMatriculasActivasPorGrupo($grupoId);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_notas_grupo_' . $grupo->grupo->codigo . '.csv"',
        ];

        $callback = function() use ($matriculas) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['codigo_estudiante', 'nota_final']);
            
            foreach ($matriculas as $mat) {
                fputcsv($file, [
                    $mat->matriculaPeriodo->matriculaCarrera->usuario->codigo_estudiante ?? '',
                    $mat->nota_final ?? ''
                ]);
            }
            
            fclose($file);
        };

        return [
            'callback' => $callback,
            'headers' => $headers,
        ];
    }

    /**
     * Import student grades via CSV file.
     */
    public function importarNotas(UploadedFile $file, int $grupoId): array
    {
        $path = $file->getRealPath();

        // Detect separator
        $content = file_get_contents($path);
        $firstLine = strtok($content, "\r\n");
        $separator = ',';
        if (str_contains($firstLine, ';')) {
            $separator = ';';
        } elseif (str_contains($firstLine, "\t")) {
            $separator = "\t";
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return ['success' => false, 'errors' => ['archivo' => 'No se pudo abrir el archivo.']];
        }

        // Read header
        $header = fgetcsv($handle, 1000, $separator);
        if (!$header) {
            fclose($handle);
            return ['success' => false, 'errors' => ['archivo' => 'El archivo está vacío o no tiene formato válido.']];
        }

        // Strip UTF-8 BOM from the first header if present
        $bom = pack('H*', 'EFBBBF');
        $header[0] = preg_replace("/^$bom/", '', $header[0]);

        // Map headers
        $header = array_map(function($h) {
            $cleaned = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $h);
            return strtolower(trim($cleaned, " \t\n\r\0\x0B\"'"));
        }, $header);

        $codigoIdx = array_search('codigo_estudiante', $header);
        if ($codigoIdx === false) {
            $codigoIdx = array_search('codigo', $header);
        }

        $notaIdx = array_search('nota_final', $header);
        if ($notaIdx === false) {
            $notaIdx = array_search('nota', $header);
        }

        if ($codigoIdx === false || $notaIdx === false) {
            fclose($handle);
            return [
                'success' => false,
                'errors' => ['archivo' => 'El archivo debe contener las columnas: codigo_estudiante y nota_final.']
            ];
        }

        $importedCount = 0;
        $errors = [];
        $rowNum = 1;

        \DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, $separator)) !== false) {
                $rowNum++;
                if (count($row) < max($codigoIdx, $notaIdx) + 1) {
                    continue;
                }

                $codigoEstudiante = trim($row[$codigoIdx]);
                $notaVal = trim($row[$notaIdx]);

                if ($codigoEstudiante === '') {
                    continue;
                }

                // 1. Find user by student code using repository
                $studentUser = $this->userRepository->obtenerPorCodigoEstudiante($codigoEstudiante);
                if (!$studentUser) {
                    $errors[] = "Fila {$rowNum}: No se encontró ningún estudiante con el código '{$codigoEstudiante}'.";
                    continue;
                }

                // 2. Find MatriculaGrupo within this group for this student using repository
                $matGrupo = $this->repository->obtenerMatriculaGrupoPorEstudiante($grupoId, $studentUser->id);

                if (!$matGrupo) {
                    $errors[] = "Fila {$rowNum}: El estudiante '{$studentUser->name}' (Código: {$codigoEstudiante}) no está inscrito en este grupo.";
                    continue;
                }

                $notaFinal = null;
                $estado = $matGrupo->estado;

                if ($notaVal !== '') {
                    if (!is_numeric($notaVal) || $notaVal < 0 || $notaVal > 100) {
                        $errors[] = "Fila {$rowNum}: La nota '{$notaVal}' debe ser un número entre 0 y 100.";
                        continue;
                    }
                    $notaFinal = intval($notaVal);
                    $estado = $notaFinal >= 51 ? 'aprobado' : 'reprobado';
                }

                $matGrupo->update([
                    'nota_final' => $notaFinal,
                    'estado' => $estado
                ]);

                $importedCount++;
            }
            fclose($handle);

            if (!empty($errors)) {
                \DB::rollBack();
                return ['success' => false, 'import_errors' => $errors];
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            fclose($handle);
            return ['success' => false, 'errors' => ['archivo' => 'Error al procesar el archivo: ' . $e->getMessage()]];
        }

        return ['success' => true, 'count' => $importedCount];
    }

    /**
     * Withdraw student from a group.
     */
    public function retirarMatriculaGrupo(MatriculaGrupo $matriculaGrupo): bool
    {
        return $this->repository->retirarMatriculaGrupo($matriculaGrupo);
    }
}
