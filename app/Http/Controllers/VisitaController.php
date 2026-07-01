<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use App\Services\ContadorVisitasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VisitaController extends Controller
{
    /**
     * Store a new visit record.
     */
    public function store(Request $request, ContadorVisitasService $service): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|string',
        ]);

        $url = $validated['url'];

        try {
            $visitedUrls = session()->get('visited_urls', []);

            if (!in_array($url, $visitedUrls)) {
                $service->registrarVisita($request, $url);
                session()->push('visited_urls', $url);
            }

            $visitsCount = Visita::where('url', $url)->count();

            return response()->json([
                'success' => true,
                'visits_count' => $visitsCount,
            ]);
        } catch (\Exception $e) {
            //  Log::error('Error registering visit: ' . $e->getMessage());

            return response()->json(['success' => false], 500);
        }
    }
}
