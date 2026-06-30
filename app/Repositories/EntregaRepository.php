<?php

namespace App\Repositories;

use App\Models\Entrega;

class EntregaRepository
{
    /**
     * Store a new assignment delivery.
     */
    public function guardar(array $data): Entrega
    {
        return Entrega::create($data);
    }

    /**
     * Update qualification of a delivery.
     */
    public function actualizarCalificacion(Entrega $entrega, array $data): bool
    {
        return $entrega->update($data);
    }
}
