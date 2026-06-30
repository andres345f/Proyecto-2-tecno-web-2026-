<?php

use App\Models\OfertaAcademica;
use App\Models\PeriodoAcademico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PeriodoAcademico CRUD operations', function () {

    // --- Authorization Tests ---

    it('requires authentication to access periodos academicos index', function () {
        $response = $this->get('/periodos-academicos');
        $response->assertRedirect('/login');
    });

    it('allows secretaria to access periodos academicos index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/periodos-academicos');
        $response->assertOk();
    });

    it('allows director to access periodos academicos index', function () {
        $user = User::factory()->create(['is_director' => true]);
        $this->actingAs($user);

        $response = $this->get('/periodos-academicos');
        $response->assertOk();
    });

    it('allows propietario to access periodos academicos index', function () {
        $user = User::factory()->create(['is_propietario' => true]);
        $this->actingAs($user);

        $response = $this->get('/periodos-academicos');
        $response->assertOk();
    });

    it('denies student access to periodos academicos index', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $response = $this->get('/periodos-academicos');
        $response->assertStatus(403);
    });

    it('denies profesor access to periodos academicos index', function () {
        $user = User::factory()->create(['is_profesor' => true]);
        $this->actingAs($user);

        $response = $this->get('/periodos-academicos');
        $response->assertStatus(403);
    });

    // --- Create Tests ---

    it('shows create periodo academico form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->get("/periodos-academicos/create?oferta_academica_id={$oferta->id}");
        $response->assertOk();
    });

    it('creates a new periodo academico with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->post('/periodos-academicos', [
            'oferta_academica_id' => $oferta->id,
            'nombre' => 'Semestre 2026-I',
            'tipo' => 'semestral',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
            'fecha_inicio_inscripcion' => '2026-01-01',
            'fecha_fin_inscripcion' => '2026-01-31',
            'fecha_inicio_cierre' => '2026-06-15',
            'fecha_fin_cierre' => '2026-06-30',
            'fecha_inicio_retiro' => '2026-02-15',
            'fecha_fin_retiro' => '2026-03-15',
            'numero_maximo_materias' => 5,
            'estado' => 'inscripcion',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('periodos_academicos', [
            'oferta_academica_id' => $oferta->id,
            'nombre' => 'Semestre 2026-I',
            'tipo' => 'semestral',
            'fecha_inicio_inscripcion' => '2026-01-01 00:00:00',
            'fecha_fin_inscripcion' => '2026-01-31 00:00:00',
            'numero_maximo_materias' => 5,
            'estado' => 'inscripcion',
        ]);
    });

    // --- Read Tests ---

    it('shows periodo academico details', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $periodo = PeriodoAcademico::factory()->create();

        $response = $this->get("/periodos-academicos/{$periodo->id}");
        $response->assertOk();
    });

    it('lists periodos academicos on index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        PeriodoAcademico::factory()->count(3)->create();

        $response = $this->get('/periodos-academicos');
        $response->assertOk();
    });

    // --- Update Tests ---

    it('shows edit periodo academico form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $periodo = PeriodoAcademico::factory()->create();

        $response = $this->get("/periodos-academicos/{$periodo->id}/edit");
        $response->assertOk();
    });

    it('updates a periodo academico with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $periodo = PeriodoAcademico::factory()->create(['nombre' => 'Old Name']);

        $response = $this->put("/periodos-academicos/{$periodo->id}", [
            'oferta_academica_id' => $periodo->oferta_academica_id,
            'nombre' => 'New Name',
            'tipo' => $periodo->tipo,
            'fecha_inicio' => $periodo->fecha_inicio->format('Y-m-d'),
            'fecha_fin' => $periodo->fecha_fin->format('Y-m-d'),
            'fecha_inicio_inscripcion' => '2026-01-05',
            'fecha_fin_inscripcion' => '2026-02-05',
            'fecha_inicio_cierre' => '2026-06-10',
            'fecha_fin_cierre' => '2026-06-20',
            'fecha_inicio_retiro' => '2026-02-10',
            'fecha_fin_retiro' => '2026-03-10',
            'numero_maximo_materias' => 6,
            'estado' => 'cierre',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('periodos_academicos', [
            'id' => $periodo->id,
            'nombre' => 'New Name',
            'fecha_inicio_inscripcion' => '2026-01-05 00:00:00',
            'fecha_fin_inscripcion' => '2026-02-05 00:00:00',
            'numero_maximo_materias' => 6,
            'estado' => 'cierre',
        ]);
    });

    // --- Delete Tests ---

    it('deletes a periodo academico via soft delete', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $periodo = PeriodoAcademico::factory()->create();

        $response = $this->delete("/periodos-academicos/{$periodo->id}");
        $response->assertRedirect();
        $this->assertSoftDeleted('periodos_academicos', ['id' => $periodo->id]);
    });

    // --- Validation Tests ---

    it('validates required fields for periodo academico creation', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/periodos-academicos', []);
        $response->assertSessionHasErrors(['oferta_academica_id', 'nombre', 'tipo', 'fecha_inicio', 'fecha_fin']);
    });

    it('validates tipo is a valid enum value', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->post('/periodos-academicos', [
            'oferta_academica_id' => $oferta->id,
            'nombre' => 'Test Periodo',
            'tipo' => 'invalido',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
        ]);

        $response->assertSessionHasErrors(['tipo']);
    });

    it('validates fecha_fin is after fecha_inicio', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->post('/periodos-academicos', [
            'oferta_academica_id' => $oferta->id,
            'nombre' => 'Test Periodo',
            'tipo' => 'semestral',
            'fecha_inicio' => '2026-06-30',
            'fecha_fin' => '2026-02-01',
        ]);

        $response->assertSessionHasErrors(['fecha_fin']);
    });

    it('validates oferta_academica_id exists', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/periodos-academicos', [
            'oferta_academica_id' => 99999,
            'nombre' => 'Test Periodo',
            'tipo' => 'semestral',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-06-30',
        ]);

        $response->assertSessionHasErrors(['oferta_academica_id']);
    });

    // --- Scoped by Oferta Tests ---

    it('scopes periodos by oferta academica on index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        PeriodoAcademico::factory()->count(2)->create(['oferta_academica_id' => $oferta->id]);
        PeriodoAcademico::factory()->count(3)->create(); // Different offering

        $response = $this->get("/periodos-academicos?oferta_academica_id={$oferta->id}");
        $response->assertOk();
    });
});
