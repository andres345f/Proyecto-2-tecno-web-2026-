<?php

namespace Database\Seeders;

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PagoSeeder extends Seeder
{
    public function run(): void
    {
        $cuotas = Cuota::with('matriculaPeriodo.periodoAcademico')->get();

        foreach ($cuotas as $cuota) {
            $period = $cuota->matriculaPeriodo->periodoAcademico;
            $isHistorical = str_contains($period->nombre, '2025-I') || str_contains($period->nombre, '2025-II') || str_contains($period->nombre, '2026-I');

            if ($isHistorical) {
                // All historical fees are paid
                $cuota->update(['estado' => 'pagado']);

                Pago::create([
                    'cuota_id' => $cuota->id,
                    'monto_pagado' => $cuota->monto,
                    'metodo_pago' => 'qr_pagofacil',
                    'transaccion_id' => 'PF-' . strtoupper(uniqid('HSEED' . $cuota->id)),
                    'fecha_pago' => Carbon::parse($cuota->fecha_vencimiento)->setTime(10, 0, 0),
                    'estado' => 'completado',
                ]);
            } else {
                // Current active period (2026-I)
                $desc = $cuota->descripcion;
                $shouldPay = str_contains($desc, 'Matrícula') || str_contains($desc, 'Cuota 1') || str_contains($desc, 'Cuota 2');

                if ($shouldPay) {
                    $cuota->update(['estado' => 'pagado']);

                    Pago::create([
                        'cuota_id' => $cuota->id,
                        'monto_pagado' => $cuota->monto,
                        'metodo_pago' => 'qr_pagofacil',
                        'transaccion_id' => 'PF-' . strtoupper(uniqid('ASEED' . $cuota->id)),
                        'fecha_pago' => Carbon::parse($cuota->fecha_vencimiento)->setTime(10, 0, 0),
                        'estado' => 'completado',
                    ]);
                } else {
                    // Remaining fees are pending
                    $cuota->update(['estado' => 'pendiente']);

                    Pago::create([
                        'cuota_id' => $cuota->id,
                        'monto_pagado' => $cuota->monto,
                        'metodo_pago' => 'qr_pagofacil',
                        'transaccion_id' => 'PF-' . strtoupper(uniqid('PSEED' . $cuota->id)),
                        'fecha_pago' => Carbon::parse($cuota->fecha_vencimiento)->setTime(12, 0, 0),
                        'estado' => 'pendiente',
                    ]);
                }
            }
        }
    }
}
