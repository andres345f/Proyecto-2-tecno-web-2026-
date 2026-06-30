<?php

use App\Models\Grupo;
use App\Models\OfertaAcademica;
use App\Models\PeriodoAcademico;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PeriodoAcademico model', function () {

    it('has table name periodos_academicos', function () {
        $periodo = PeriodoAcademico::factory()->create();
        expect($periodo->getTable())->toBe('periodos_academicos');
    });

    it('has fillable attributes', function () {
        $periodo = new PeriodoAcademico;
        expect($periodo->getFillable())->toContain(
            'oferta_academica_id',
            'nombre',
            'tipo',
            'fecha_inicio',
            'fecha_fin',
        );
    });

    it('belongs to an OfertaAcademica', function () {
        $oferta = OfertaAcademica::factory()->create();
        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);

        $relation = $periodo->ofertaAcademica();
        expect($relation)->toBeInstanceOf(BelongsTo::class);
        expect($periodo->ofertaAcademica->id)->toBe($oferta->id);
    });



    it('uses soft deletes', function () {
        $periodo = PeriodoAcademico::factory()->create();
        $periodo->delete();

        $this->assertSoftDeleted('periodos_academicos', ['id' => $periodo->id]);
        expect($periodo->trashed())->toBeTrue();
    });
});
