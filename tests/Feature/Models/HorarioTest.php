<?php

use App\Models\Aula;
use App\Models\Horario;
use App\Models\GrupoPeriodo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Horario model', function () {

    it('has correct fillable attributes', function () {
        $horario = new Horario;
        expect($horario->getFillable())->toBe([
            'grupo_periodo_id',
            'dia',
            'hora_inicio',
            'hora_fin',
            'aula_id',
        ]);
    });

    it('belongs to grupoPeriodo', function () {
        $aula = Aula::factory()->create();
        $horario = Horario::factory()->create(['aula_id' => $aula->id]);

        expect($horario->grupoPeriodo)->toBeInstanceOf(GrupoPeriodo::class);
    });

    it('belongs to aula', function () {
        $aula = Aula::factory()->create(['nombre' => 'Lab 101']);
        $horario = Horario::factory()->create(['aula_id' => $aula->id]);

        expect($horario->aula->nombre)->toBe('Lab 101');
    });

    it('stores dia correctly', function () {
        $aula = Aula::factory()->create();
        $horario = Horario::factory()->create([
            'aula_id' => $aula->id,
            'dia' => 'Miércoles',
        ]);

        expect($horario->dia)->toBe('Miércoles');
    });

    it('stores time values correctly', function () {
        $aula = Aula::factory()->create();
        $horario = Horario::factory()->create([
            'aula_id' => $aula->id,
            'hora_inicio' => '08:00',
            'hora_fin' => '09:30',
        ]);

        expect($horario->hora_inicio)->toBe('08:00');
        expect($horario->hora_fin)->toBe('09:30');
    });
});
