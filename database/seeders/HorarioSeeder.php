<?php

namespace Database\Seeders;

use App\Models\Aula;
use App\Models\GrupoPeriodo;
use App\Models\Horario;
use Illuminate\Database\Seeder;

class HorarioSeeder extends Seeder
{
    public function run(): void
    {
        $aulas = Aula::all();
        $grupoPeriodos = GrupoPeriodo::all();

        $dayPairs = [
            ['Lunes', 'Miércoles'],
            ['Martes', 'Jueves'],
            ['Viernes', 'Sábado'],
        ];

        $shifts = [
            ['inicio' => '08:00', 'fin' => '09:30'],
            ['inicio' => '09:40', 'fin' => '11:10'],
            ['inicio' => '11:20', 'fin' => '12:50'],
            ['inicio' => '14:00', 'fin' => '15:30'],
            ['inicio' => '15:40', 'fin' => '17:10'],
            ['inicio' => '17:20', 'fin' => '18:50'],
        ];

        // Track allocations to prevent clashes
        $classroomBusy = [];
        $teacherBusy = [];

        foreach ($grupoPeriodos as $grupoPeriodo) {
            $allocated = false;
            
            // Generate a time key based on the period dates to block concurrent physical time
            $periodo = $grupoPeriodo->periodoAcademico;
            $timeKey = \Carbon\Carbon::parse($periodo->fecha_inicio)->format('Y-m-d') . '_' . \Carbon\Carbon::parse($periodo->fecha_fin)->format('Y-m-d');

            foreach ($aulas as $aula) {
                foreach ($dayPairs as $dayPair) {
                    foreach ($shifts as $shiftIndex => $shift) {
                        $day1 = $dayPair[0];
                        $day2 = $dayPair[1];

                        // Check if classroom is free on both days at this shift
                        $aulaFree1 = !isset($classroomBusy[$aula->id][$timeKey][$day1][$shiftIndex]);
                        $aulaFree2 = !isset($classroomBusy[$aula->id][$timeKey][$day2][$shiftIndex]);

                        // Check if teacher is free on both days at this shift
                        $teacherFree1 = !isset($teacherBusy[$grupoPeriodo->docente_id][$timeKey][$day1][$shiftIndex]);
                        $teacherFree2 = !isset($teacherBusy[$grupoPeriodo->docente_id][$timeKey][$day2][$shiftIndex]);

                        if ($aulaFree1 && $aulaFree2 && $teacherFree1 && $teacherFree2) {
                            // Create the two schedules
                            Horario::create([
                                'dia' => $day1,
                                'hora_inicio' => $shift['inicio'],
                                'hora_fin' => $shift['fin'],
                                'aula_id' => $aula->id,
                                'grupo_periodo_id' => $grupoPeriodo->id,
                            ]);

                            Horario::create([
                                'dia' => $day2,
                                'hora_inicio' => $shift['inicio'],
                                'hora_fin' => $shift['fin'],
                                'aula_id' => $aula->id,
                                'grupo_periodo_id' => $grupoPeriodo->id,
                            ]);

                            // Mark as busy per period
                            $classroomBusy[$aula->id][$timeKey][$day1][$shiftIndex] = true;
                            $classroomBusy[$aula->id][$timeKey][$day2][$shiftIndex] = true;

                            $teacherBusy[$grupoPeriodo->docente_id][$timeKey][$day1][$shiftIndex] = true;
                            $teacherBusy[$grupoPeriodo->docente_id][$timeKey][$day2][$shiftIndex] = true;

                            $allocated = true;
                            break 3; // Break out of shift, dayPair, aula loops
                        }
                    }
                }
            }

            if (!$allocated) {
                throw new \Exception("Could not allocate schedule for group period {$grupoPeriodo->id}");
            }
        }
    }
}
