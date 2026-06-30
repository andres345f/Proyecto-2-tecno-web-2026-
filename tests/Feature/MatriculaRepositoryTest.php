<?php

use App\Models\MatriculaCarrera;
use App\Models\OfertaAcademica;
use App\Models\User;
use App\Repositories\MatriculaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new MatriculaRepository;
});

describe('MatriculaRepository', function () {

    it('counts total active matriculas carrera', function () {
        // Arrange: 3 active + 1 inactive (different users for unique constraint)
        $oferta = OfertaAcademica::factory()->create();

        $user1 = User::factory()->create(['is_estudiante' => true]);
        $user2 = User::factory()->create(['is_estudiante' => true]);
        $user3 = User::factory()->create(['is_estudiante' => true]);
        $user4 = User::factory()->create(['is_estudiante' => true]);

        MatriculaCarrera::create([
            'usuario_id' => $user1->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);
        MatriculaCarrera::create([
            'usuario_id' => $user2->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);
        MatriculaCarrera::create([
            'usuario_id' => $user3->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);
        MatriculaCarrera::create([
            'usuario_id' => $user4->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'inactivo',
        ]);

        $result = $this->repo->totalMatriculasActivas();

        expect($result)->toBe(3);
    });

    it('counts matriculas grouped by oferta academica', function () {
        $user1 = User::factory()->create(['is_estudiante' => true]);
        $user2 = User::factory()->create(['is_estudiante' => true]);
        $oferta1 = OfertaAcademica::factory()->create(['nombre' => 'TEC-RED']);
        $oferta2 = OfertaAcademica::factory()->create(['nombre' => 'ING-SIS']);

        MatriculaCarrera::create([
            'usuario_id' => $user1->id,
            'oferta_academica_id' => $oferta1->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);
        MatriculaCarrera::create([
            'usuario_id' => $user2->id,
            'oferta_academica_id' => $oferta1->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);
        MatriculaCarrera::create([
            'usuario_id' => $user1->id,
            'oferta_academica_id' => $oferta2->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        $result = $this->repo->matriculasPorOferta();

        expect($result)->toHaveCount(2);
        // First result should be TEC-RED with 2 matriculas
        $tecRed = $result->firstWhere('oferta_academica_id', $oferta1->id);
        expect($tecRed->oferta_count)->toBe(2);
        // Second result should be ING-SIS with 1 matricula
        $ingSis = $result->firstWhere('oferta_academica_id', $oferta2->id);
        expect($ingSis->oferta_count)->toBe(1);
    });

    it('counts unique students per offering', function () {
        $user1 = User::factory()->create(['is_estudiante' => true]);
        $user2 = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();

        MatriculaCarrera::create([
            'usuario_id' => $user1->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);
        MatriculaCarrera::create([
            'usuario_id' => $user2->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        $result = $this->repo->alumnosPorOferta();

        expect($result)->toHaveCount(1);
        expect($result->first()->alumnos_count)->toBe(2);
    });

    it('returns zero when no active matriculas exist', function () {
        $result = $this->repo->totalMatriculasActivas();

        expect($result)->toBe(0);
    });
});
