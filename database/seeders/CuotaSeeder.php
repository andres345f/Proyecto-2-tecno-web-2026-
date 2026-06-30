<?php

namespace Database\Seeders;

use App\Models\MatriculaPeriodo;
use App\Services\GeneradorCuotasService;
use Illuminate\Database\Seeder;

class CuotaSeeder extends Seeder
{
    public function run(): void
    {
        $service = new GeneradorCuotasService;

        $matriculas = MatriculaPeriodo::all();

        foreach ($matriculas as $matricula) {
            $service->generar($matricula);
        }
    }
}
