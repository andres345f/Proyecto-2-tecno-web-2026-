<?php

namespace Database\Seeders;

use App\Models\Entrega;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Database\Seeder;

class EntregaSeeder extends Seeder
{
    public function run(): void
    {
        $carlos = User::where('email', 'carlos@est.com')->first();
        $david = User::where('email', 'david@est.com')->first();
        $ana = User::where('email', 'ana@est.com')->first();

        /*
        // Tasks
        $parcialProg2 = Tarea::where('titulo', 'Parcial 1 - Programación II')->first();
        $practicaProg2 = Tarea::where('titulo', 'Práctica 1 - Patrones GoF')->first();
        $proyectoDesign = Tarea::where('titulo', 'Proyecto 1 - Retícula Editorial')->first();

        // Carlos submissions
        if ($parcialProg2 && $carlos) {
            Entrega::create([
                'tarea_id' => $parcialProg2->id,
                'usuario_id' => $carlos->id,
                'ruta_archivo' => 'tareas/'.$parcialProg2->id.'/'.$carlos->id.'_parcial1.pdf',
                'fecha_entrega' => now()->subDays(2),
                'nota' => 88.00,
                'retroalimentacion' => 'Excelente implementación. Los diagramas UML son claros y el código cumple con los patrones requeridos.',
            ]);
        }

        if ($practicaProg2 && $carlos) {
            Entrega::create([
                'tarea_id' => $practicaProg2->id,
                'usuario_id' => $carlos->id,
                'ruta_archivo' => 'tareas/'.$practicaProg2->id.'/'.$carlos->id.'_practica1.pdf',
                'fecha_entrega' => now()->subDay(),
                'nota' => null,
                'retroalimentacion' => null,
            ]);
        }

        // David submissions
        if ($parcialProg2 && $david) {
            Entrega::create([
                'tarea_id' => $parcialProg2->id,
                'usuario_id' => $david->id,
                'ruta_archivo' => 'tareas/'.$parcialProg2->id.'/'.$david->id.'_parcial1.pdf',
                'fecha_entrega' => now()->subDays(2),
                'nota' => 75.00,
                'retroalimentacion' => 'El código funciona, pero falta aplicar correctamente el patrón Factory Method en la clase Factura.',
            ]);
        }

        // Ana submissions
        if ($proyectoDesign && $ana) {
            Entrega::create([
                'tarea_id' => $proyectoDesign->id,
                'usuario_id' => $ana->id,
                'ruta_archivo' => 'tareas/'.$proyectoDesign->id.'/'.$ana->id.'_reticula.pdf',
                'fecha_entrega' => now()->subDays(3),
                'nota' => 95.00,
                'retroalimentacion' => 'Excelente manejo de la tipografía y la distribución de los espacios blancos. Muy profesional.',
            ]);
        }
        */

        // Tasks
        $parcialEstrat = Tarea::where('titulo', 'Parcial 1 - Estrategia de Marketing')->first();
        $practicaEstrat = Tarea::where('titulo', 'Práctica 1 - Plan de Inbound Marketing')->first();
        $proyectoSocial = Tarea::where('titulo', 'Proyecto 1 - Gestión de Redes Sociales')->first();

        // Carlos submissions
        if ($parcialEstrat && $carlos) {
            Entrega::create([
                'tarea_id' => $parcialEstrat->id,
                'usuario_id' => $carlos->id,
                'ruta_archivo' => 'tareas/'.$parcialEstrat->id.'/'.$carlos->id.'_parcial1.pdf',
                'fecha_entrega' => now()->subDays(2),
                'nota' => 88.00,
                'retroalimentacion' => 'Excelente propuesta de valor y análisis detallado del mercado objetivo.',
            ]);
        }

        if ($practicaEstrat && $carlos) {
            Entrega::create([
                'tarea_id' => $practicaEstrat->id,
                'usuario_id' => $carlos->id,
                'ruta_archivo' => 'tareas/'.$practicaEstrat->id.'/'.$carlos->id.'_practica1.pdf',
                'fecha_entrega' => now()->subDay(),
                'nota' => null,
                'retroalimentacion' => null,
            ]);
        }

        // David submissions
        if ($parcialEstrat && $david) {
            Entrega::create([
                'tarea_id' => $parcialEstrat->id,
                'usuario_id' => $david->id,
                'ruta_archivo' => 'tareas/'.$parcialEstrat->id.'/'.$david->id.'_parcial1.pdf',
                'fecha_entrega' => now()->subDays(2),
                'nota' => 75.00,
                'retroalimentacion' => 'El análisis es correcto, pero falto detallar los canales de adquisición digital.',
            ]);
        }

        // Ana submissions
        if ($proyectoSocial && $ana) {
            Entrega::create([
                'tarea_id' => $proyectoSocial->id,
                'usuario_id' => $ana->id,
                'ruta_archivo' => 'tareas/'.$proyectoSocial->id.'/'.$ana->id.'_social.pdf',
                'fecha_entrega' => now()->subDays(3),
                'nota' => 95.00,
                'retroalimentacion' => 'Excelente planeación mensual y gran calidad estética en el contenido propuesto.',
            ]);
        }
    }
}
