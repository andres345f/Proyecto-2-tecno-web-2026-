<?php

namespace App\Services;

use App\Models\Cuota;
use App\Models\MatriculaPeriodo;
use Illuminate\Support\Collection;

class GeneradorCuotasService
{
    public function generar(MatriculaPeriodo $matriculaPeriodo): Collection
    {
        $planPago = $matriculaPeriodo->planPago;
        $periodoAcademico = $matriculaPeriodo->periodoAcademico;
        $fechaMatricula = $matriculaPeriodo->fecha_matricula;

        $cuotas = collect();

        // Cuota 0: Matrícula
        $cuotas->push(new Cuota([
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'descripcion' => "Matrícula - {$periodoAcademico->nombre}",
            'monto' => $planPago->monto_matricula,
            'fecha_vencimiento' => $fechaMatricula->toDateString(),
            'estado' => 'pendiente',
        ]));

        // Cuotas 1..N
        for ($i = 1; $i <= $planPago->cantidad_cuotas; $i++) {
            $cuotas->push(new Cuota([
                'matricula_periodo_id' => $matriculaPeriodo->id,
                'descripcion' => "Cuota {$i} - {$periodoAcademico->nombre}",
                'monto' => $planPago->monto_cuota,
                'fecha_vencimiento' => $fechaMatricula->copy()->addDays(30 * $i)->toDateString(),
                'estado' => 'pendiente',
            ]));
        }

        // Bulk insert
        Cuota::insert($cuotas->toArray());

        return Cuota::where('matricula_periodo_id', $matriculaPeriodo->id)->get();
    }
}
