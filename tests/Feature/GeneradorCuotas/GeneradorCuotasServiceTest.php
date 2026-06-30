<?php

use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\OfertaAcademica;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use App\Models\User;
use App\Services\GeneradorCuotasService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GeneradorCuotasService', function () {

    it('generates correct number of cuotas (1 matrícula + N cuotas)', function () {
        $oferta = OfertaAcademica::factory()->create();
        $student = User::factory()->create(['is_estudiante' => true]);
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'monto_matricula' => 50.00,
            'monto_cuota' => 30.00,
            'cantidad_cuotas' => 5,
        ]);

        $matriculaPeriodo = MatriculaPeriodo::create([
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => '2026-01-28 10:00:00',
            'estado' => 'activo',
        ]);

        $service = new GeneradorCuotasService;
        $cuotas = $service->generar($matriculaPeriodo);

        expect($cuotas)->toHaveCount(6); // 1 matrícula + 5 cuotas
    });

    it('creates matrícula cuota first with correct amount', function () {
        $oferta = OfertaAcademica::factory()->create();
        $student = User::factory()->create(['is_estudiante' => true]);
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $periodo = PeriodoAcademico::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'nombre' => 'Semestre 2026-I',
        ]);
        $plan = PlanPago::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'monto_matricula' => 50.00,
            'monto_cuota' => 30.00,
            'cantidad_cuotas' => 5,
        ]);

        $matriculaPeriodo = MatriculaPeriodo::create([
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => '2026-01-28 10:00:00',
            'estado' => 'activo',
        ]);

        $service = new GeneradorCuotasService;
        $cuotas = $service->generar($matriculaPeriodo);

        $matriculacuota = $cuotas->first();
        expect($matriculacuota->descripcion)->toBe('Matrícula - Semestre 2026-I');
        expect($matriculacuota->monto)->toBe('50.00');
        expect($matriculacuota->fecha_vencimiento->toDateString())->toBe('2026-01-28');
        expect($matriculacuota->estado)->toBe('pendiente');
    });

    it('creates installment cuotas with correct amounts', function () {
        $oferta = OfertaAcademica::factory()->create();
        $student = User::factory()->create(['is_estudiante' => true]);
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'monto_matricula' => 50.00,
            'monto_cuota' => 30.00,
            'cantidad_cuotas' => 5,
        ]);

        $matriculaPeriodo = MatriculaPeriodo::create([
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => '2026-01-28 10:00:00',
            'estado' => 'activo',
        ]);

        $service = new GeneradorCuotasService;
        $cuotas = $service->generar($matriculaPeriodo);

        $installmentCuotas = $cuotas->slice(1);
        foreach ($installmentCuotas as $cuota) {
            expect($cuota->monto)->toBe('30.00');
            expect($cuota->estado)->toBe('pendiente');
        }
    });

    it('sets correct vencimiento dates with 30-day intervals', function () {
        $oferta = OfertaAcademica::factory()->create();
        $student = User::factory()->create(['is_estudiante' => true]);
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'monto_matricula' => 50.00,
            'monto_cuota' => 30.00,
            'cantidad_cuotas' => 5,
        ]);

        $matriculaPeriodo = MatriculaPeriodo::create([
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => '2026-01-28 10:00:00',
            'estado' => 'activo',
        ]);

        $service = new GeneradorCuotasService;
        $cuotas = $service->generar($matriculaPeriodo);

        // Matrícula: vence el mismo día
        expect($cuotas[0]->fecha_vencimiento->toDateString())->toBe('2026-01-28');

        // Cuota 1: +30 días
        expect($cuotas[1]->fecha_vencimiento->toDateString())->toBe('2026-02-27');

        // Cuota 2: +60 días
        expect($cuotas[2]->fecha_vencimiento->toDateString())->toBe('2026-03-29');

        // Cuota 3: +90 días
        expect($cuotas[3]->fecha_vencimiento->toDateString())->toBe('2026-04-28');

        // Cuota 4: +120 días
        expect($cuotas[4]->fecha_vencimiento->toDateString())->toBe('2026-05-28');

        // Cuota 5: +150 días
        expect($cuotas[5]->fecha_vencimiento->toDateString())->toBe('2026-06-27');
    });

    it('uses periodo name in cuota descriptions', function () {
        $oferta = OfertaAcademica::factory()->create();
        $student = User::factory()->create(['is_estudiante' => true]);
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $periodo = PeriodoAcademico::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'nombre' => 'Año Académico 2026',
        ]);
        $plan = PlanPago::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'monto_matricula' => 100.00,
            'monto_cuota' => 120.00,
            'cantidad_cuotas' => 3,
        ]);

        $matriculaPeriodo = MatriculaPeriodo::create([
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => '2026-01-29 10:00:00',
            'estado' => 'activo',
        ]);

        $service = new GeneradorCuotasService;
        $cuotas = $service->generar($matriculaPeriodo);

        expect($cuotas[0]->descripcion)->toBe('Matrícula - Año Académico 2026');
        expect($cuotas[1]->descripcion)->toBe('Cuota 1 - Año Académico 2026');
        expect($cuotas[2]->descripcion)->toBe('Cuota 2 - Año Académico 2026');
        expect($cuotas[3]->descripcion)->toBe('Cuota 3 - Año Académico 2026');
    });

    it('persists cuotas to database', function () {
        $oferta = OfertaAcademica::factory()->create();
        $student = User::factory()->create(['is_estudiante' => true]);
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'monto_matricula' => 25.00,
            'monto_cuota' => 15.00,
            'cantidad_cuotas' => 2,
        ]);

        $matriculaPeriodo = MatriculaPeriodo::create([
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => '2026-01-30 10:00:00',
            'estado' => 'activo',
        ]);

        $service = new GeneradorCuotasService;
        $service->generar($matriculaPeriodo);

        $this->assertDatabaseCount('cuotas', 3); // 1 matrícula + 2 cuotas
        $this->assertDatabaseHas('cuotas', [
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'descripcion' => 'Matrícula - '.$periodo->nombre,
            'monto' => 25.00,
            'estado' => 'pendiente',
        ]);
    });
});
