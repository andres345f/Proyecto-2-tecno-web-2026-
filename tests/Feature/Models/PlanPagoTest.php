<?php

use App\Models\OfertaAcademica;
use App\Models\PlanPago;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PlanPago model', function () {

    it('has table name planes_pago', function () {
        $plan = PlanPago::factory()->create();
        expect($plan->getTable())->toBe('planes_pago');
    });

    it('has fillable attributes', function () {
        $plan = new PlanPago;
        expect($plan->getFillable())->toContain(
            'oferta_academica_id',
            'nombre',
            'tipo',
            'monto_matricula',
            'monto_cuota',
            'cantidad_cuotas',
        );
    });

    it('belongs to an OfertaAcademica', function () {
        $oferta = OfertaAcademica::factory()->create();
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

        $relation = $plan->ofertaAcademica();
        expect($relation)->toBeInstanceOf(BelongsTo::class);
        expect($plan->ofertaAcademica->id)->toBe($oferta->id);
    });

    it('uses soft deletes', function () {
        $plan = PlanPago::factory()->create();
        $plan->delete();

        $this->assertSoftDeleted('planes_pago', ['id' => $plan->id]);
        expect($plan->trashed())->toBeTrue();
    });
});
