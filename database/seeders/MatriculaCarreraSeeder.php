<?php

namespace Database\Seeders;

use App\Models\MatriculaCarrera;
use App\Models\OfertaAcademica;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MatriculaCarreraSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get main users and careers
        $carlos = User::where('email', 'carlos@est.com')->first();
        $ana = User::where('email', 'ana@est.com')->first();
        $david = User::where('email', 'david@est.com')->first();

        /*
        $tsDs = OfertaAcademica::where('codigo', 'TS-DS')->first();
        $tmRt = OfertaAcademica::where('codigo', 'TS-RT')->first();
        */
        $tmDg = OfertaAcademica::where('codigo', 'TM-DG')->first();
        $taMd = OfertaAcademica::where('codigo', 'TA-MD')->first();

        // Enroll main students (historical since 2025)
        MatriculaCarrera::create([
            'usuario_id' => $carlos->id,
            'oferta_academica_id' => $taMd->id,
            'fecha_matricula' => '2025-01-15 10:00:00',
            'estado' => 'activo',
        ]);

        MatriculaCarrera::create([
            'usuario_id' => $ana->id,
            'oferta_academica_id' => $taMd->id,
            'fecha_matricula' => '2025-01-20 10:00:00',
            'estado' => 'activo',
        ]);

        MatriculaCarrera::create([
            'usuario_id' => $david->id,
            'oferta_academica_id' => $taMd->id,
            'fecha_matricula' => '2025-01-25 10:00:00',
            'estado' => 'activo',
        ]);

        // 2. Generate 100 random student users and matriculate them
        $careers = [$taMd, $tmDg];
        $password = Hash::make('password');

        for ($i = 1; $i <= 100; $i++) {
            $career = $careers[array_rand($careers)];
            
            // Determine starting year: 50% start in 2025, 50% start in 2026
            $startYear = ($i <= 50) ? 2025 : 2026;
            $month = str_pad(rand(1, 2), 2, '0', STR_PAD_LEFT);
            $day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
            $fechaMatricula = "{$startYear}-{$month}-{$day} 09:00:00";

            $student = User::create([
                'name' => fake()->name(),
                'email' => "estudiante{$i}@instituto.edu",
                'password' => $password,
                'is_propietario' => false,
                'is_director' => false,
                'is_secretaria' => false,
                'is_profesor' => false,
                'is_estudiante' => true,
                'codigo_estudiante' => 'EST-' . (10000 + $i),
                'is_activo' => true,
            ]);

            MatriculaCarrera::create([
                'usuario_id' => $student->id,
                'oferta_academica_id' => $career->id,
                'fecha_matricula' => $fechaMatricula,
                'estado' => 'activo',
            ]);
        }
    }
}
