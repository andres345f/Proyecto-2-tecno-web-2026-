<?php

namespace App\Services;

use App\Models\Entrega;
use App\Models\Tarea;
use App\Repositories\EntregaRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntregaService
{
    protected EntregaRepository $entregaRepository;

    public function __construct(EntregaRepository $entregaRepository)
    {
        $this->entregaRepository = $entregaRepository;
    }

    /**
     * Create a new delivery, uploading the file.
     */
    public function crearEntrega(int $userId, int $tareaId, UploadedFile $archivo): Entrega
    {
        // Encontrar la tarea para asegurar que exista
        $tarea = Tarea::findOrFail($tareaId);

        $nombreArchivo = $userId . '_' . $archivo->getClientOriginalName();
        $ruta = 'tareas/' . $tarea->id . '/' . $nombreArchivo;

        Storage::disk('local')->put($ruta, file_get_contents($archivo));

        return $this->entregaRepository->guardar([
            'tarea_id' => $tarea->id,
            'usuario_id' => $userId,
            'ruta_archivo' => $ruta,
            'fecha_entrega' => now(),
        ]);
    }

    /**
     * Grade a delivery.
     */
    public function calificarEntrega(Entrega $entrega, array $data): bool
    {
        return $this->entregaRepository->actualizarCalificacion($entrega, [
            'nota' => $data['nota'],
            'retroalimentacion' => $data['retroalimentacion'] ?? null,
        ]);
    }

    /**
     * Get path for downloading file.
     *
     * @throws NotFoundHttpException
     */
    public function obtenerRutaDescarga(Entrega $entrega): string
    {
        if (!$entrega->ruta_archivo || !Storage::disk('local')->exists($entrega->ruta_archivo)) {
            throw new NotFoundHttpException('Archivo no encontrado.');
        }

        return $entrega->ruta_archivo;
    }

    /**
     * Load details/relations for a delivery.
     */
    public function cargarDetalles(Entrega $entrega): Entrega
    {
        return $entrega->load(['tarea.grupoPeriodo.grupo.materia', 'usuario']);
    }
}
