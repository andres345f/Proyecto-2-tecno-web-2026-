<?php

use App\Models\Materia;
use App\Models\OfertaAcademica;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('OfertaAcademica model', function () {

    it('has correct fillable attributes', function () {
        $oferta = new OfertaAcademica;
        expect($oferta->getFillable())->toBe([
            'nombre',
            'codigo',
            'descripcion',
        ]);
    });

    it('uses soft deletes', function () {
        $oferta = OfertaAcademica::factory()->create();
        $id = $oferta->id;

        $oferta->delete();

        $this->assertSoftDeleted('ofertas_academicas', ['id' => $id]);
    });

    it('can restore soft deleted record', function () {
        $oferta = OfertaAcademica::factory()->create();
        $id = $oferta->id;

        $oferta->delete();
        $this->assertSoftDeleted('ofertas_academicas', ['id' => $id]);

        OfertaAcademica::withTrashed()->find($id)->restore();
        $this->assertDatabaseHas('ofertas_academicas', ['id' => $id, 'deleted_at' => null]);
    });

    it('loads materias relationship via malla curricular', function () {
        $oferta = OfertaAcademica::factory()->create();
        $materia1 = Materia::factory()->create();
        $materia2 = Materia::factory()->create();

        $oferta->materias()->attach($materia1->id, ['semestre_orden' => 1]);
        $oferta->materias()->attach($materia2->id, ['semestre_orden' => 2]);

        $oferta->refresh();
        expect($oferta->materias)->toHaveCount(2);
        expect($oferta->materias->first()->pivot->semestre_orden)->toBe(1);
        expect($oferta->materias->last()->pivot->semestre_orden)->toBe(2);
    });

    it('returns empty collection when no materias', function () {
        $oferta = OfertaAcademica::factory()->create();

        expect($oferta->materias)->toHaveCount(0);
    });

    it('can be created with all attributes', function () {
        $oferta = OfertaAcademica::create([
            'nombre' => 'Técnico en Redes',
            'codigo' => 'TEC-RED',
            'descripcion' => 'Carrera técnica',
        ]);

        expect($oferta->nombre)->toBe('Técnico en Redes');
        expect($oferta->codigo)->toBe('TEC-RED');
        expect($oferta->descripcion)->toBe('Carrera técnica');
    });
});
