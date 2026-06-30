<?php

use App\Models\Grupo;
use App\Models\GrupoPeriodo;
use App\Models\MatriculaGrupo;
use App\Models\MatriculaPeriodo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Docente y Estudiante groups view access', function () {

    it('denies unauthenticated access to docente groups', function () {
        $response = $this->get('/grupos-docente');
        $response->assertRedirect('/login');
    });

    it('allows a teacher to access their own groups list', function () {
        $docente = User::factory()->create(['is_profesor' => true]);
        $this->actingAs($docente);

        $grupoPeriodo = GrupoPeriodo::factory()->create(['docente_id' => $docente->id]);

        $response = $this->get('/grupos-docente');
        $response->assertOk();
    });

    it('denies students from accessing docente groups list', function () {
        $student = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($student);

        $response = $this->get('/grupos-docente');
        $response->assertStatus(403);
    });

    it('allows teacher to view their own group detail and enrolled students', function () {
        $docente = User::factory()->create(['is_profesor' => true]);
        $this->actingAs($docente);

        $grupoPeriodo = GrupoPeriodo::factory()->create(['docente_id' => $docente->id]);
        $student = User::factory()->create(['is_estudiante' => true]);
        $matriculaPeriodo = MatriculaPeriodo::factory()->create();
        
        $matriculaGrupo = MatriculaGrupo::create([
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'grupo_periodo_id' => $grupoPeriodo->id,
            'estado' => 'en_curso',
        ]);

        $response = $this->get("/grupos-docente/{$grupoPeriodo->id}");
        $response->assertOk();
    });

    it('denies teacher from viewing another teacher group details', function () {
        $docente1 = User::factory()->create(['is_profesor' => true]);
        $docente2 = User::factory()->create(['is_profesor' => true]);
        $this->actingAs($docente1);

        $grupoPeriodo = GrupoPeriodo::factory()->create(['docente_id' => $docente2->id]);

        $response = $this->get("/grupos-docente/{$grupoPeriodo->id}");
        $response->assertStatus(403);
    });

    it('allows student to access their own mis-grupos list', function () {
        $student = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($student);

        $response = $this->get('/mis-grupos');
        $response->assertOk();
    });
});
