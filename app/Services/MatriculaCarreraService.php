<?php

namespace App\Services;

use App\Models\MatriculaCarrera;
use App\Models\User;
use App\Repositories\MatriculaCarreraRepository;
use App\Repositories\UserRepository;
use App\Repositories\OfertaAcademicaRepository;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;

class MatriculaCarreraService
{
    protected MatriculaCarreraRepository $matriculaRepository;
    protected UserRepository $userRepository;
    protected OfertaAcademicaRepository $ofertaRepository;

    public function __construct(
        MatriculaCarreraRepository $matriculaRepository,
        UserRepository $userRepository,
        OfertaAcademicaRepository $ofertaRepository
    ) {
        $this->matriculaRepository = $matriculaRepository;
        $this->userRepository = $userRepository;
        $this->ofertaRepository = $ofertaRepository;
    }

    /**
     * Get list of enrollments based on user permissions.
     */
    public function listarMatriculas(User $user): Collection
    {
        if ($user->is_estudiante && !$user->is_secretaria && !$user->is_director && !$user->is_propietario) {
            return $this->matriculaRepository->obtenerPorUsuarioConRelaciones($user->id);
        }

        return $this->matriculaRepository->obtenerTodasConRelaciones();
    }

    /**
     * Get paginated and filtered enrollments.
     */
    public function listarMatriculasPaginadas(User $user, int $perPage, ?string $search = null, ?string $estado = null)
    {
        $usuarioId = null;
        if ($user->is_estudiante && !$user->is_secretaria && !$user->is_director && !$user->is_propietario) {
            $usuarioId = $user->id;
        }

        return $this->matriculaRepository->obtenerPaginadasConFiltros($usuarioId, $perPage, $search, $estado);
    }

    /**
     * Get students and offerings for create form.
     */
    public function obtenerDatosFormulario(): array
    {
        return [
            'usuarios' => $this->userRepository->obtenerTodosPorRol('estudiante'),
            'ofertas' => $this->ofertaRepository->obtenerTodasConMateriasCount(),
        ];
    }

    /**
     * Create a new student enrollment.
     */
    public function crearMatricula(array $data): MatriculaCarrera
    {
        return $this->matriculaRepository->guardar([
            'usuario_id' => $data['usuario_id'],
            'oferta_academica_id' => $data['oferta_academica_id'],
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);
    }

    /**
     * Load details of an enrollment.
     */
    public function cargarDetalles(MatriculaCarrera $matricula): MatriculaCarrera
    {
        return $matricula->load(['usuario', 'ofertaAcademica', 'matriculasPeriodo.periodoAcademico', 'matriculasPeriodo.planPago']);
    }

    /**
     * Update enrollment state.
     */
    public function actualizarEstado(MatriculaCarrera $matricula, string $estado): bool
    {
        return $this->matriculaRepository->actualizar($matricula, ['estado' => $estado]);
    }

    /**
     * Delete an enrollment.
     */
    public function eliminarMatricula(MatriculaCarrera $matricula): bool
    {
        return $this->matriculaRepository->eliminar($matricula);
    }

    /**
     * Process student CSV import.
     */
    public function importarEstudiantesYMatriculas(UploadedFile $file): array
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
        $header = array_map(function ($h) {
            $cleaned = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $h);
            return strtolower(trim($cleaned, " \t\n\r\0\x0B\"'"));
        }, $header);

        $nameIdx = array_search('nombre', $header);
        if ($nameIdx === false)
            $nameIdx = array_search('name', $header);

        $emailIdx = array_search('email', $header);

        $ofertaIdx = array_search('codigo_oferta', $header);
        if ($ofertaIdx === false)
            $ofertaIdx = array_search('carrera_codigo', $header);
        if ($ofertaIdx === false)
            $ofertaIdx = array_search('carrera', $header);

        if ($nameIdx === false || $emailIdx === false || $ofertaIdx === false) {
            fclose($handle);
            return [
                'success' => false,
                'errors' => ['archivo' => 'El archivo debe contener las columnas: nombre, email, y codigo_oferta (o carrera_codigo).']
            ];
        }

        $importedCount = 0;
        $errors = [];
        $rowNum = 1;

        \DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, $separator)) !== false) {
                $rowNum++;
                if (count($row) < max($nameIdx, $emailIdx, $ofertaIdx) + 1) {
                    continue;
                }

                $name = trim($row[$nameIdx]);
                $email = trim($row[$emailIdx]);
                $ofertaCodigo = trim($row[$ofertaIdx]);

                if (empty($name) || empty($email) || empty($ofertaCodigo)) {
                    continue;
                }

                // Check email using UserRepository
                if ($this->userRepository->existeEmail($email)) {
                    $errors[] = "Fila {$rowNum}: El correo '{$email}' ya está registrado.";
                    continue;
                }

                // Find Oferta Academica using OfertaAcademicaRepository
                $oferta = $this->ofertaRepository->obtenerPorCodigo($ofertaCodigo);
                if (!$oferta) {
                    $errors[] = "Fila {$rowNum}: No se encontró la carrera con código '{$ofertaCodigo}'.";
                    continue;
                }

                // Generate code
                do {
                    $codigoEstudiante = $this->generarCodigoEstudiante($oferta->codigo);
                } while ($this->userRepository->existeCodigoEstudiante($codigoEstudiante));

                // Create user using UserRepository
                $user = $this->userRepository->guardar([
                    'name' => $name,
                    'email' => $email,
                    'password' => \Hash::make($codigoEstudiante),
                    'is_estudiante' => true,
                    'codigo_estudiante' => $codigoEstudiante,
                    'is_activo' => true,
                ]);

                // Create matricula using MatriculaCarreraRepository
                $this->matriculaRepository->guardar([
                    'usuario_id' => $user->id,
                    'oferta_academica_id' => $oferta->id,
                    'fecha_matricula' => now(),
                    'estado' => 'activo',
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
     * Generate student code.
     */
    private function generarCodigoEstudiante(string $ofertaCodigo): string
    {
        $year = date('y'); // e.g. "26"
        $cleanOferta = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $ofertaCodigo));
        $carreraPart = substr($cleanOferta, 0, 3);

        $lengthNeeded = 9 - strlen($year) - strlen($carreraPart);
        if ($lengthNeeded < 0) {
            $lengthNeeded = 0;
        }

        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomPart = '';
        for ($i = 0; $i < $lengthNeeded; $i++) {
            $randomPart .= $characters[rand(0, strlen($characters) - 1)];
        }

        $code = $year . $carreraPart . $randomPart;
        if (strlen($code) < 9) {
            $code = str_pad($code, 9, 'X');
        }

        return substr($code, 0, 9);
    }
}
