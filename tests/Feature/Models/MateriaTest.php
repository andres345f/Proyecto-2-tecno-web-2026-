<?php

use App\Models\Materia;
use App\Models\MallaCurricular;
use App\Models\OfertaAcademica;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Materia model', function () {

    it('has fillable attributes', function () {
        $materia = new Materia;

        expect($materia->getFillable())->toContain('nombre')
            ->and($materia->getFillable())->toContain('codigo')
            ->and($materia->getFillable())->toContain('descripcion');
    });

    it('casts attributes correctly', function () {
        $materia = Materia::factory()->create([
            'nombre' => 'Física Aplicada',
            'codigo' => 'FIS-101',
            'descripcion' => 'Curso de física',
        ]);

        expect($materia->nombre)->toBe('Física Aplicada')
            ->and($materia->codigo)->toBe('FIS-101')
            ->and($materia->descripcion)->toBe('Curso de física');
    });

    it('uses soft deletes', function () {
        $materia = Materia::factory()->create();
        $materiaId = $materia->id;

        $materia->delete();

        $this->assertDatabaseHas('materias', ['id' => $materiaId]);
        $this->assertSoftDeleted('materias', ['id' => $materiaId]);
    });

    it('has prerrequisitos relationship via malla curricular', function () {
        $oferta = OfertaAcademica::factory()->create();
        $calculoI = Materia::factory()->create(['codigo' => 'MAT-101']);
        $calculoII = Materia::factory()->create(['codigo' => 'MAT-102']);

        $mallaI = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $calculoI->id,
            'semestre_orden' => 1,
        ]);

        $mallaII = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $calculoII->id,
            'semestre_orden' => 2,
        ]);

        $mallaII->prerrequisitos()->attach($mallaI->id);

        expect($mallaII->prerrequisitos)->toHaveCount(1)
            ->and($mallaII->prerrequisitos->first()->id)->toBe($mallaI->id);
    });

    it('has esPrerequisitoDe relationship via malla curricular', function () {
        $oferta = OfertaAcademica::factory()->create();
        $calculoI = Materia::factory()->create(['codigo' => 'MAT-101']);
        $calculoII = Materia::factory()->create(['codigo' => 'MAT-102']);
        $calculoIII = Materia::factory()->create(['codigo' => 'MAT-103']);

        $mallaI = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $calculoI->id,
            'semestre_orden' => 1,
        ]);

        $mallaII = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $calculoII->id,
            'semestre_orden' => 2,
        ]);

        $mallaIII = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $calculoIII->id,
            'semestre_orden' => 3,
        ]);

        $mallaII->prerrequisitos()->attach($mallaI->id);
        $mallaIII->prerrequisitos()->attach($mallaI->id);

        expect($mallaI->esPrerequisitoDe)->toHaveCount(2);
    });

    it('supports multiple prerequisites via malla curricular', function () {
        $oferta = OfertaAcademica::factory()->create();
        $fisica = Materia::factory()->create(['codigo' => 'FIS-101']);
        $matematicas = Materia::factory()->create(['codigo' => 'MAT-101']);
        $calculoII = Materia::factory()->create(['codigo' => 'CAL-201']);

        $mallaFisica = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $fisica->id,
            'semestre_orden' => 1,
        ]);

        $mallaMatematicas = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $matematicas->id,
            'semestre_orden' => 1,
        ]);

        $mallaCalculoII = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $calculoII->id,
            'semestre_orden' => 2,
        ]);

        $mallaCalculoII->prerrequisitos()->attach([
            $mallaFisica->id,
            $mallaMatematicas->id
        ]);

        expect($mallaCalculoII->prerrequisitos)->toHaveCount(2);
    });

    it('supports a malla entry being prerequisite for multiple malla entries', function () {
        $oferta = OfertaAcademica::factory()->create();
        $calculoI = Materia::factory()->create(['codigo' => 'CAL-101']);
        $calculoII = Materia::factory()->create(['codigo' => 'CAL-201']);
        $calculoIII = Materia::factory()->create(['codigo' => 'CAL-301']);

        $mallaI = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $calculoI->id,
            'semestre_orden' => 1,
        ]);

        $mallaII = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $calculoII->id,
            'semestre_orden' => 2,
        ]);

        $mallaIII = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $calculoIII->id,
            'semestre_orden' => 3,
        ]);

        $mallaII->prerrequisitos()->attach($mallaI->id);
        $mallaIII->prerrequisitos()->attach($mallaI->id);

        $mallaI->refresh();
        expect($mallaI->esPrerequisitoDe)->toHaveCount(2);
    });

    it('has nullable descripcion', function () {
        $materia = Materia::factory()->create([
            'descripcion' => null,
        ]);

        expect($materia->descripcion)->toBeNull();
    });
});
