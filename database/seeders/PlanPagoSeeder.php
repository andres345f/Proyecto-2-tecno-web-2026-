<?php

namespace Database\Seeders;

use App\Models\OfertaAcademica;
use App\Models\PlanPago;
use Illuminate\Database\Seeder;

class PlanPagoSeeder extends Seeder
{
    public function run(): void
    {
        $careers = OfertaAcademica::all();

        foreach ($careers as $career) {
            PlanPago::create([
                'oferta_academica_id' => $career->id,
                'nombre' => "Regular - {$career->codigo}",
                'tipo' => 'por_periodo',
                'monto_matricula' => 0.01,
                'monto_cuota' => 0.01,
                'cantidad_cuotas' => 5,
            ]);

            PlanPago::create([
                'oferta_academica_id' => $career->id,
                'nombre' => "Beca 50% - {$career->codigo}",
                'tipo' => 'especial',
                'monto_matricula' => 0.01,
                'monto_cuota' => 0.01,
                'cantidad_cuotas' => 5,
            ]);
        }
    }
}
