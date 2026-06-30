<?php

use App\Models\GrupoPeriodo;
use App\Models\MatriculaGrupo;
use App\Models\MatriculaPeriodo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MatriculaGrupo model', function () {

    it('has table name matriculas_grupo', function () {
        $matriculaGrupo = MatriculaGrupo::factory()->create();
        expect($matriculaGrupo->getTable())->toBe('matriculas_grupo');
    });

    it('has fillable attributes', function () {
        $matriculaGrupo = new MatriculaGrupo;
        expect($matriculaGrupo->getFillable())->toContain(
            'matricula_periodo_id',
            'grupo_periodo_id',
            'nota_final',
            'estado',
        );
    });

    it('belongs to a MatriculaPeriodo', function () {
        $matriculaPeriodo = MatriculaPeriodo::factory()->create();
        $matriculaGrupo = MatriculaGrupo::factory()->create(['matricula_periodo_id' => $matriculaPeriodo->id]);

        $relation = $matriculaGrupo->matriculaPeriodo();
        expect($relation)->toBeInstanceOf(BelongsTo::class);
        expect($matriculaGrupo->matriculaPeriodo->id)->toBe($matriculaPeriodo->id);
    });

    it('belongs to a GrupoPeriodo', function () {
        $grupoPeriodo = GrupoPeriodo::factory()->create();
        $matriculaGrupo = MatriculaGrupo::factory()->create(['grupo_periodo_id' => $grupoPeriodo->id]);

        $relation = $matriculaGrupo->grupoPeriodo();
        expect($relation)->toBeInstanceOf(BelongsTo::class);
        expect($matriculaGrupo->grupoPeriodo->id)->toBe($grupoPeriodo->id);
    });

    it('has decimal cast for nota_final', function () {
        $matriculaGrupo = MatriculaGrupo::factory()->create(['nota_final' => 85.50]);
        expect($matriculaGrupo->nota_final)->toBe('85.50');
    });

    it('defaults estado to en_curso', function () {
        $matriculaGrupo = MatriculaGrupo::factory()->create(['estado' => 'en_curso']);
        expect($matriculaGrupo->estado)->toBe('en_curso');
    });
});
