<?php

namespace Database\Seeders;

use App\Models\Grupo;
use App\Models\GrupoPeriodo;
use App\Models\Materia;
use App\Models\PeriodoAcademico;
use App\Models\User;
use Illuminate\Database\Seeder;

class GrupoSeeder extends Seeder
{
    public function run(): void
    {
        // Get / Create teachers
        $docentes = [];
        
        $juan = User::where('email', 'juan@doc.com')->first();
        if ($juan) {
            $docentes[] = $juan;
        }

        // Generate 24 additional teachers to avoid schedule clashes
        for ($i = 1; $i <= 24; $i++) {
            $docentes[] = User::firstOrCreate(
                ['email' => "profesor{$i}@doc.com"],
                [
                    'name' => "Prof. " . fake()->name(),
                    'password' => \Hash::make('password'),
                    'is_propietario' => false,
                    'is_director' => false,
                    'is_secretaria' => false,
                    'is_profesor' => true,
                    'is_estudiante' => false,
                    'is_activo' => true,
                ]
            );
        }

        // Get all subjects
        $materias = Materia::with('ofertasAcademicas')->get();
        $docenteIndex = 0;

        foreach ($materias as $materia) {
            // Create Group 1
            $g1 = Grupo::create([
                'codigo' => "{$materia->codigo}-G1",
                'materia_id' => $materia->id,
            ]);

            // Create Group 2
            $g2 = Grupo::create([
                'codigo' => "{$materia->codigo}-G2",
                'materia_id' => $materia->id,
            ]);

            // Get academic periods for the ofertas academicas of this materia
            $periodos = PeriodoAcademico::whereIn('oferta_academica_id', $materia->ofertasAcademicas->pluck('id'))->get();

            foreach ($periodos as $periodo) {
                // Offer both groups in each period
                GrupoPeriodo::create([
                    'grupo_id' => $g1->id,
                    'periodo_academico_id' => $periodo->id,
                    'docente_id' => $docentes[$docenteIndex % count($docentes)]->id,
                    'cupo_maximo' => 35,
                ]);
                $docenteIndex++;

                GrupoPeriodo::create([
                    'grupo_id' => $g2->id,
                    'periodo_academico_id' => $periodo->id,
                    'docente_id' => $docentes[$docenteIndex % count($docentes)]->id,
                    'cupo_maximo' => 35,
                ]);
                $docenteIndex++;
            }
        }
    }
}
