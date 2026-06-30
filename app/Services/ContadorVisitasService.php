<?php

namespace App\Services;

use App\Models\Visita;
use Illuminate\Http\Request;

class ContadorVisitasService
{
    /**
     * Register a visit for the given URL.
     */
    public function registrarVisita(Request $request, string $url): void
    {
        Visita::create([
            'url' => $url,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'usuario_id' => $request->user()?->id,
        ]);
    }
}
