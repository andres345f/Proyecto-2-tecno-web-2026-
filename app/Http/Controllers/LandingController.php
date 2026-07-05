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
            'institutionName' => 'Instituto Educa Y Forma',
            'tagline' => 'Formando el futuro de la educación',
            'features' => [
                [
                    'title' => 'Oferta Académica y Mallas',
                    'description' => 'Programas de estudio como Diseño Gráfico y Marketing Digital con control estricto de prerrequisitos y malla curricular.',
                    'icon' => 'academic',
                ],
                [
                    'title' => 'Inscripciones y Matrículas',
                    'description' => 'Gestión completa del ciclo de vida del estudiante desde su matriculación en carrera hasta la asignación de grupos.',
                    'icon' => 'enrollment',
                ],
                [
                    'title' => 'Planes de Pago y Cuotas',
                    'description' => 'Control financiero con planes de pago personalizados, control de vencimientos y generación de códigos QR para transacciones.',
                    'icon' => 'payment',
                ],
                [
                    'title' => 'Planificación de Horarios',
                    'description' => 'Programación eficiente de turnos y aulas con validación automática de choques de docentes y disponibilidad física.',
                    'icon' => 'schedule',
                ],
                [
                    'title' => 'Gestión de Tareas y Entregas',
                    'description' => 'Plataforma para que docentes publiquen actividades y califiquen entregas con retroalimentación personalizada.',
                    'icon' => 'homework',
                ],
                [
                    'title' => 'Reportes y Analíticas',
                    'description' => 'Visualización de estadísticas en tiempo real y exportación de reportes de rendimiento académico y cobranza.',
                    'icon' => 'reports',
                ],
            ],
        ]);
    }
}
