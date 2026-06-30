<?php

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('Pago model', function () {

    it('has table name pagos', function () {
        $pago = Pago::factory()->create();
        expect($pago->getTable())->toBe('pagos');
    });

    it('has fillable attributes', function () {
        $pago = new Pago;
        expect($pago->getFillable())->toContain(
            'cuota_id',
            'monto_pagado',
            'metodo_pago',
            'transaccion_id',
            'fecha_pago',
            'estado',
        );
    });

    it('belongs to a Cuota', function () {
        $cuota = Cuota::factory()->create();
        $pago = Pago::factory()->create(['cuota_id' => $cuota->id]);

        $relation = $pago->cuota();
        expect($relation)->toBeInstanceOf(BelongsTo::class);
        expect($pago->cuota->id)->toBe($cuota->id);
    });

    it('casts monto_pagado to decimal', function () {
        $pago = Pago::factory()->create(['monto_pagado' => '50.00']);
        expect($pago->monto_pagado)->toBe('50.00');
    });

    it('casts fecha_pago to datetime', function () {
        $pago = Pago::factory()->create(['fecha_pago' => '2026-06-25 10:00:00']);
        expect($pago->fecha_pago)->toBeInstanceOf(Carbon::class);
    });

    it('has default metodo_pago of qr_pagofacil via DB default', function () {
        DB::table('pagos')->insert([
            'cuota_id' => Cuota::factory()->create()->id,
            'monto_pagado' => 50.00,
            'transaccion_id' => 'PF-TEST1',
            'fecha_pago' => now(),
        ]);
        $row = DB::table('pagos')->where('transaccion_id', 'PF-TEST1')->first();
        expect($row->metodo_pago)->toBe('qr_pagofacil');
    });

    it('has default estado of pendiente', function () {
        DB::table('pagos')->insert([
            'cuota_id' => Cuota::factory()->create()->id,
            'monto_pagado' => 50.00,
            'transaccion_id' => 'PF-TEST2',
            'fecha_pago' => now(),
        ]);
        $row = DB::table('pagos')->where('transaccion_id', 'PF-TEST2')->first();
        expect($row->estado)->toBe('pendiente');
    });
});
