<?php

use App\Models\Cuota;
use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\OfertaAcademica;
use App\Models\Pago;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createDirectorUser(): User
{
    return User::factory()->create(['is_director' => true]);
}

describe('ReporteController', function () {

    it('allows director to view reportes page', function () {
        $admin = createDirectorUser();

        $response = $this->actingAs($admin)->get(route('reportes.index'));

        $response->assertOk();
    });

    it('allows propietario to view reportes page', function () {
        $admin = User::factory()->create(['is_propietario' => true]);

        $response = $this->actingAs($admin)->get(route('reportes.index'));

        $response->assertOk();
    });

    it('rejects student from accessing reportes', function () {
        $student = User::factory()->create(['is_estudiante' => true]);

        $response = $this->actingAs($student)->get(route('reportes.index'));

        $response->assertForbidden();
    });

    it('rejects unauthenticated access to reportes', function () {
        $response = $this->get(route('reportes.index'));

        $response->assertRedirect('/login');
    });

    it('passes total recaudado in response', function () {
        $admin = createDirectorUser();
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

        $mp = MatriculaPeriodo::create([
            'matricula_carrera_id' => $carrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        $cuota = Cuota::create([
            'matricula_periodo_id' => $mp->id,
            'descripcion' => 'Test',
            'monto' => 100.00,
            'fecha_vencimiento' => now()->addMonth(),
            'estado' => 'pagado',
        ]);

        Pago::create([
            'cuota_id' => $cuota->id,
            'monto_pagado' => 100.00,
            'metodo_pago' => 'qr_pagofacil',
            'transaccion_id' => 'TX-123',
            'fecha_pago' => now(),
            'estado' => 'completado',
        ]);

        $response = $this->actingAs($admin)->get(route('reportes.index'));

        $response->assertOk();
    });

    it('passes matriculas por oferta in response', function () {
        $admin = createDirectorUser();
        $user = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();

        MatriculaCarrera::create([
            'usuario_id' => $user->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($admin)->get(route('reportes.index'));

        $response->assertOk();
    });
});
