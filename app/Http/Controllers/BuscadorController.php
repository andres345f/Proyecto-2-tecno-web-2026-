<?php

namespace App\Http\Controllers;

use App\Services\BuscadorGlobalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuscadorController extends Controller
{
    public function __construct(
        private BuscadorGlobalService $buscadorService
    ) {}

    /**
     * Search across students, materias, and cuotas.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1',
        ]);

        $results = $this->buscadorService->search($request->q);

        return response()->json($results);
    }
}
