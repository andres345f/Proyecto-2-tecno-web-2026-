<?php

use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\OfertaAcademica;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

describe('MatriculaCarrera model', function () {

    it('has table name matriculas_carrera', function () {
        $matricula = MatriculaCarrera::factory()->create();
        expect($matricula->getTable())->toBe('matriculas_carrera');
    });

    it('has fillable attributes', function () {
        $matricula = new MatriculaCarrera;
        expect($matricula->getFillable())->toContain(
            'usuario_id',
            'oferta_academica_id',
            'fecha_matricula',
            'estado',
        );
    });

    it('belongs to a User (usuario)', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $matricula = MatriculaCarrera::factory()->create(['usuario_id' => $user->id]);

        $relation = $matricula->usuario();
        expect($relation)->toBeInstanceOf(BelongsTo::class);
        expect($matricula->usuario->id)->toBe($user->id);
    });

    it('belongs to an OfertaAcademica', function () {
        $oferta = OfertaAcademica::factory()->create();
        $matricula = MatriculaCarrera::factory()->create(['oferta_academica_id' => $oferta->id]);

        $relation = $matricula->ofertaAcademica();
        expect($relation)->toBeInstanceOf(BelongsTo::class);
        expect($matricula->ofertaAcademica->id)->toBe($oferta->id);
    });

    it('has many MatriculaPeriodo', function () {
        $matricula = MatriculaCarrera::factory()->create();
        MatriculaPeriodo::factory()->count(2)->create(['matricula_carrera_id' => $matricula->id]);

        $relation = $matricula->matriculasPeriodo();
        expect($relation)->toBeInstanceOf(HasMany::class);
        expect($matricula->matriculasPeriodo)->toHaveCount(2);
    });

    it('casts fecha_matricula to datetime', function () {
        $matricula = MatriculaCarrera::factory()->create([
            'fecha_matricula' => '2026-01-15 10:00:00',
        ]);

        expect($matricula->fecha_matricula)->toBeInstanceOf(Carbon::class);
    });
});
