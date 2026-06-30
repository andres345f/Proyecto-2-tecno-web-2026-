<?php

use App\Models\MallaCurricular;
use App\Models\Materia;
use App\Models\OfertaAcademica;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MallaCurricular model', function () {

    it('has correct fillable attributes', function () {
        $malla = new MallaCurricular;
        expect($malla->getFillable())->toBe([
            'oferta_academica_id',
            'materia_id',
            'semestre_orden',
        ]);
    });

    it('belongs to oferta academica', function () {
        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        $malla = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);

        expect($malla->ofertaAcademica->id)->toBe($oferta->id);
    });

    it('belongs to materia', function () {
        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        $malla = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);

        expect($malla->materia->id)->toBe($materia->id);
    });

    it('stores semestre_orden correctly', function () {
        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        $malla = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 3,
        ]);

        expect($malla->semestre_orden)->toBe(3);
    });

    it('enforces unique constraint on oferta_academica_id and materia_id', function () {
        $this->expectException(QueryException::class);

        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);

        // Try to create duplicate
        MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 2,
        ]);
    });

    it('allows same materia in different ofertas academicas', function () {
        $oferta1 = OfertaAcademica::factory()->create();
        $oferta2 = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        MallaCurricular::create([
            'oferta_academica_id' => $oferta1->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);

        $malla2 = MallaCurricular::create([
            'oferta_academica_id' => $oferta2->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 2,
        ]);

        expect($malla2->semestre_orden)->toBe(2);
        expect(MallaCurricular::where('materia_id', $materia->id)->count())->toBe(2);
    });
});
