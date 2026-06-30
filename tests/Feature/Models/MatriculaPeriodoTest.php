<?php

use App\Models\Cuota;
use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

describe('MatriculaPeriodo model', function () {

    it('has table name matriculas_periodo', function () {
        $matricula = MatriculaPeriodo::factory()->create();
        expect($matricula->getTable())->toBe('matriculas_periodo');
    });

    it('has fillable attributes', function () {
        $matricula = new MatriculaPeriodo;
        expect($matricula->getFillable())->toContain(
            'matricula_carrera_id',
            'periodo_academico_id',
            'plan_pago_id',
            'fecha_matricula',
            'estado',
        );
    });

    it('belongs to a MatriculaCarrera', function () {
        $carrera = MatriculaCarrera::factory()->create();
        $matricula = MatriculaPeriodo::factory()->create(['matricula_carrera_id' => $carrera->id]);

        $relation = $matricula->matriculaCarrera();
        expect($relation)->toBeInstanceOf(BelongsTo::class);
        expect($matricula->matriculaCarrera->id)->toBe($carrera->id);
    });

    it('belongs to a PeriodoAcademico', function () {
        $periodo = PeriodoAcademico::factory()->create();
        $matricula = MatriculaPeriodo::factory()->create(['periodo_academico_id' => $periodo->id]);

        $relation = $matricula->periodoAcademico();
        expect($relation)->toBeInstanceOf(BelongsTo::class);
        expect($matricula->periodoAcademico->id)->toBe($periodo->id);
    });

    it('belongs to a PlanPago', function () {
        $plan = PlanPago::factory()->create();
        $matricula = MatriculaPeriodo::factory()->create(['plan_pago_id' => $plan->id]);

        $relation = $matricula->planPago();
        expect($relation)->toBeInstanceOf(BelongsTo::class);
        expect($matricula->planPago->id)->toBe($plan->id);
    });

    it('has many Cuotas', function () {
        $matricula = MatriculaPeriodo::factory()->create();
        Cuota::factory()->count(3)->create(['matricula_periodo_id' => $matricula->id]);

        $relation = $matricula->cuotas();
        expect($relation)->toBeInstanceOf(HasMany::class);
        expect($matricula->cuotas)->toHaveCount(3);
    });

    it('casts fecha_matricula to datetime', function () {
        $matricula = MatriculaPeriodo::factory()->create([
            'fecha_matricula' => '2026-01-28 10:00:00',
        ]);

        expect($matricula->fecha_matricula)->toBeInstanceOf(Carbon::class);
    });
});
