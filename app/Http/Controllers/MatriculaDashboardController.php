<?php

namespace App\Http\Controllers;

use App\Models\MatriculaCarrera;
use App\Models\MatriculaGrupo;
use App\Models\MatriculaPeriodo;
use Inertia\Inertia;

class MatriculaDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_carreras' => MatriculaCarrera::count(),
            'total_periodos' => MatriculaPeriodo::count(),
            'total_grupos' => MatriculaGrupo::count(),
        ];

        return Inertia::render('Matricula/Index', [
            'stats' => $stats,
        ]);
    }
}
