<?php

use App\Models\Entrega;
use App\Models\GrupoPeriodo;
use App\Models\Tarea;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Tarea Model', function () {

    it('has correct table name', function () {
        $tarea = new Tarea;
        expect($tarea->getTable())->toBe('tareas');
    });

    it('has correct fillable attributes', function () {
        $tarea = new Tarea;
        expect($tarea->getFillable())->toBe([
            'grupo_periodo_id',
            'titulo',
            'descripcion',
            'fecha_vencimiento',
            'puntaje_maximo',
        ]);
    });

    it('belongs to a grupoPeriodo', function () {
        $tarea = Tarea::factory()->create();

        expect($tarea->grupoPeriodo)->toBeInstanceOf(GrupoPeriodo::class);
    });

    it('has many entregas', function () {
        $tarea = Tarea::factory()->create();
        Entrega::factory()->count(3)->create(['tarea_id' => $tarea->id]);

        expect($tarea->entregas)->toHaveCount(3);
    });

    it('casts fecha_vencimiento to datetime', function () {
        $tarea = Tarea::factory()->create([
            'fecha_vencimiento' => '2026-03-15 23:59:59',
        ]);

        expect($tarea->fecha_vencimiento)->toBeInstanceOf(Carbon::class);
    });

    it('casts puntaje_maximo to decimal', function () {
        $tarea = Tarea::factory()->create(['puntaje_maximo' => 100.00]);

        expect($tarea->puntaje_maximo)->toBe('100.00');
    });
});
