<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    /**
     * Display the landing page.
     */
    public function show(): Response
    {
        return Inertia::render('Landing', [
            'institutionName' => 'Instituto Educativo Futuro',
            'tagline' => 'Formando el futuro de la educación',
            'features' => [
                [
                    'title' => 'Gestión Académica',
                    'description' => 'Administra carreras, materias, grupos y horarios de forma eficiente.',
                    'icon' => 'academic',
                ],
                [
                    'title' => 'Pagos y Finanzas',
                    'description' => 'Controla matrículas, cuotas y pagos con reportes detallados.',
                    'icon' => 'payment',
                ],
                [
                    'title' => 'Reportes y Estadísticas',
                    'description' => 'Obtén insights valiosos sobre el rendimiento académico y financiero.',
                    'icon' => 'reports',
                ],
                [
                    'title' => 'Reportes y Estadísticas',
                    'description' => 'Obtén insights valiosos sobre el rendimiento académico y financiero.',
                    'icon' => 'reports',
                ],
                [
                    'title' => 'Reportes y Estadísticas',
                    'description' => 'Obtén insights valiosos sobre el rendimiento académico y financiero.',
                    'icon' => 'reports',
                ],
                [
                    'title' => 'Reportes y Estadísticas',
                    'description' => 'Obtén insights valiosos sobre el rendimiento académico y financiero.',
                    'icon' => 'reports',
                ],

                [
                    'title' => 'Reportes y Estadísticas',
                    'description' => 'Obtén insights valiosos sobre el rendimiento académico y financiero.',
                    'icon' => 'reports',
                ],
                [
                    'title' => 'Reportes y Estadísticas',
                    'description' => 'Obtén insights valiosos sobre el rendimiento académico y financiero.',
                    'icon' => 'reports',
                ],

                [
                    'title' => 'Reportes y Estadísticas',
                    'description' => 'Obtén insights valiosos sobre el rendimiento académico y financiero.',
                    'icon' => 'reports',
                ],
                [
                    'title' => 'Reportes y Estadísticas',
                    'description' => 'Obtén insights valiosos sobre el rendimiento académico y financiero.',
                    'icon' => 'reports',
                ],
                [
                    'title' => 'Reportes y Estadísticas',
                    'description' => 'Obtén insights valiosos sobre el rendimiento académico y financiero.',
                    'icon' => 'reports',
                ],
            ],
        ]);
    }
}
