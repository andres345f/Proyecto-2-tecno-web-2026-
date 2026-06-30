<?php

use App\Models\Cuota;
use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\OfertaAcademica;
use App\Models\Pago;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use App\Models\User;
use App\Repositories\CuotaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createCuotaWithPago(string $estadoPago = 'completado', float $monto = 100.00): Cuota
{
    $user = User::factory()->create(['is_estudiante' => true]);
    $oferta = OfertaAcademica::factory()->create();
    $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
    $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

    $carrera = MatriculaCarrera::create([
        'usuario_id' => $user->id,
        'oferta_academica_id' => $oferta->id,
        'fecha_matricula' => now(),
        'estado' => 'activo',
    ]);

    $matriculaPeriodo = MatriculaPeriodo::create([
        'matricula_carrera_id' => $carrera->id,
        'periodo_academico_id' => $periodo->id,
        'plan_pago_id' => $plan->id,
        'fecha_matricula' => now(),
        'estado' => 'activo',
    ]);

    $cuota = Cuota::create([
        'matricula_periodo_id' => $matriculaPeriodo->id,
        'descripcion' => 'Test Cuota',
        'monto' => $monto,
        'fecha_vencimiento' => now()->addMonth(),
        'estado' => $estadoPago === 'completado' ? 'pagado' : 'pendiente',
    ]);

    if ($estadoPago === 'completado') {
        Pago::create([
            'cuota_id' => $cuota->id,
            'monto_pagado' => $monto,
            'metodo_pago' => 'qr_pagofacil',
            'transaccion_id' => 'TX-TEST-'.$cuota->id,
            'fecha_pago' => now(),
            'estado' => 'completado',
        ]);
    }

    return $cuota;
}

beforeEach(function () {
    $this->repo = new CuotaRepository;
});

describe('CuotaRepository', function () {

    it('calculates total recaudado from completed pagos', function () {
        createCuotaWithPago('completado', 100.00);
        createCuotaWithPago('completado', 200.00);

        $result = $this->repo->totalRecaudado();

        expect($result)->toBe(300.00);
    });

    it('calculates total por cobrar from pending cuotas', function () {
        createCuotaWithPago('pendiente', 50.00);
        createCuotaWithPago('pendiente', 75.00);

        $result = $this->repo->totalPorCobrar();

        expect($result)->toBe(125.00);
    });

    it('returns zero recaudado when no completed pagos', function () {
        $result = $this->repo->totalRecaudado();

        expect($result)->toBe(0.00);
    });

    it('finds overdue cuotas with vencimiento before now', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

        $carrera = MatriculaCarrera::create([
            'usuario_id' => $user->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        $matriculaPeriodo = MatriculaPeriodo::create([
            'matricula_carrera_id' => $carrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        // Overdue cuota
        Cuota::create([
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'descripcion' => 'Overdue Cuota',
            'monto' => 50.00,
            'fecha_vencimiento' => now()->subWeek(),
            'estado' => 'pendiente',
        ]);

        // Not overdue (future vencimiento)
        Cuota::create([
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'descripcion' => 'Future Cuota',
            'monto' => 50.00,
            'fecha_vencimiento' => now()->addMonth(),
            'estado' => 'pendiente',
        ]);

        $result = $this->repo->cuotasVencidas();

        expect($result)->toHaveCount(1);
        expect($result->first()->descripcion)->toBe('Overdue Cuota');
    });

    it('groups debtor students by user with overdue amounts', function () {
        $user1 = User::factory()->create(['is_estudiante' => true, 'name' => 'Carlos']);
        $user2 = User::factory()->create(['is_estudiante' => true, 'name' => 'Ana']);
        $oferta = OfertaAcademica::factory()->create();
        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

        foreach ([$user1, $user2] as $user) {
            $carrera = MatriculaCarrera::create([
                'usuario_id' => $user->id,
                'oferta_academica_id' => $oferta->id,
                'fecha_matricula' => now(),
                'estado' => 'activo',
            ]);

            $matriculaPeriodo = MatriculaPeriodo::create([
                'matricula_carrera_id' => $carrera->id,
                'periodo_academico_id' => $periodo->id,
                'plan_pago_id' => $plan->id,
                'fecha_matricula' => now(),
                'estado' => 'activo',
            ]);

            Cuota::create([
                'matricula_periodo_id' => $matriculaPeriodo->id,
                'descripcion' => 'Deuda',
                'monto' => 100.00,
                'fecha_vencimiento' => now()->subWeek(),
                'estado' => 'pendiente',
            ]);
        }

        $result = $this->repo->alumnosDeudores();

        expect($result)->toHaveCount(2);
    });
});
