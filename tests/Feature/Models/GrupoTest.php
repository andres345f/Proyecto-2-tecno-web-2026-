<?php

use App\Models\Grupo;
use App\Models\Materia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Grupo model', function () {

    it('has table name grupos', function () {
        $grupo = Grupo::factory()->create();
        expect($grupo->getTable())->toBe('grupos');
    });

    it('has fillable attributes', function () {
        $grupo = new Grupo;
        expect($grupo->getFillable())->toContain(
            'codigo',
            'materia_id',
        );
        expect($grupo->getFillable())->not->toContain('docente_id', 'cupo_maximo');
    });

    it('belongs to a Materia', function () {
        $materia = Materia::factory()->create();
        $grupo = Grupo::factory()->create(['materia_id' => $materia->id]);

        $relation = $grupo->materia();
        expect($relation)->toBeInstanceOf(BelongsTo::class);
        expect($grupo->materia->id)->toBe($materia->id);
    });

    it('has many grupoPeriodos', function () {
        $grupo = Grupo::factory()->create();
        $relation = $grupo->grupoPeriodos();
        expect($relation)->toBeInstanceOf(HasMany::class);
    });

    it('uses soft deletes', function () {
        $grupo = Grupo::factory()->create();
        $grupo->delete();

        $this->assertSoftDeleted('grupos', ['id' => $grupo->id]);
        expect($grupo->trashed())->toBeTrue();
    });
});
