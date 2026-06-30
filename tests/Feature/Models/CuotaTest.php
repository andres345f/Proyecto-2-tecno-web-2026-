<?php

use App\Models\Cuota;
use App\Models\MatriculaPeriodo;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

describe('Cuota model', function () {

    it('has table name cuotas', function () {
        $cuota = Cuota::factory()->create();
        expect($cuota->getTable())->toBe('cuotas');
    });

    it('has fillable attributes', function () {
        $cuota = new Cuota;
        expect($cuota->getFillable())->toContain(
            'matricula_periodo_id',
            'descripcion',
            'monto',
            'fecha_vencimiento',
            'estado',
        );
    });

    it('belongs to a MatriculaPeriodo', function () {
        $matricula = MatriculaPeriodo::factory()->create();
        $cuota = Cuota::factory()->create(['matricula_periodo_id' => $matricula->id]);

        $relation = $cuota->matriculaPeriodo();
        expect($relation)->toBeInstanceOf(BelongsTo::class);
        expect($cuota->matriculaPeriodo->id)->toBe($matricula->id);
    });

    it('casts monto to decimal', function () {
        $cuota = Cuota::factory()->create(['monto' => '50.00']);
        expect($cuota->monto)->toBe('50.00');
    });

    it('casts fecha_vencimiento to date', function () {
        $cuota = Cuota::factory()->create(['fecha_vencimiento' => '2026-06-28']);
        expect($cuota->fecha_vencimiento)->toBeInstanceOf(Carbon::class);
    });

    it('has one Pago', function () {
        $cuota = Cuota::factory()->create();
        $pago = Pago::factory()->create(['cuota_id' => $cuota->id]);

        $relation = $cuota->pago();
        expect($relation)->toBeInstanceOf(HasOne::class);
        expect($cuota->pago->id)->toBe($pago->id);
    });

    it('returns null pago when not paid', function () {
        $cuota = Cuota::factory()->create();
        expect($cuota->pago)->toBeNull();
    });
});
