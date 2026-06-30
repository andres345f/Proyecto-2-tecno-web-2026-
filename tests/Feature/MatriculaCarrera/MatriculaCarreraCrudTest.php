<?php

use App\Models\MatriculaCarrera;
use App\Models\OfertaAcademica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MatriculaCarrera CRUD operations', function () {

    // --- Authorization Tests ---

    it('requires authentication to access matriculas carrera index', function () {
        $response = $this->get('/matriculas-carrera');
        $response->assertRedirect('/login');
    });

    it('allows secretaria to access matriculas carrera index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/matriculas-carrera');
        $response->assertOk();
    });

    it('allows director to access matriculas carrera index', function () {
        $user = User::factory()->create(['is_director' => true]);
        $this->actingAs($user);

        $response = $this->get('/matriculas-carrera');
        $response->assertOk();
    });

    it('denies student access to matriculas carrera index', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $response = $this->get('/matriculas-carrera');
        $response->assertStatus(403);
    });

    // --- Create Tests ---

    it('shows create matricula carrera form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/matriculas-carrera/create');
        $response->assertOk();
    });

    it('creates a new matricula carrera with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $student = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();

        $response = $this->post('/matriculas-carrera', [
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('matriculas_carrera', [
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
            'estado' => 'activo',
        ]);
    });

    // --- Read Tests ---

    it('shows matricula carrera details', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $matricula = MatriculaCarrera::factory()->create();

        $response = $this->get("/matriculas-carrera/{$matricula->id}");
        $response->assertOk();
    });

    it('lists matriculas carrera on index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        MatriculaCarrera::factory()->count(3)->create();

        $response = $this->get('/matriculas-carrera');
        $response->assertOk();
    });

    // --- Update Tests ---

    it('updates matricula carrera estado', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $matricula = MatriculaCarrera::factory()->create(['estado' => 'activo']);

        $response = $this->put("/matriculas-carrera/{$matricula->id}", [
            'estado' => 'retirado',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('matriculas_carrera', [
            'id' => $matricula->id,
            'estado' => 'retirado',
        ]);
    });

    // --- Delete Tests ---

    it('deletes a matricula carrera', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $matricula = MatriculaCarrera::factory()->create();

        $response = $this->delete("/matriculas-carrera/{$matricula->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('matriculas_carrera', ['id' => $matricula->id]);
    });

    // --- Validation Tests ---

    it('validates required fields for matricula carrera creation', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/matriculas-carrera', []);
        $response->assertSessionHasErrors(['usuario_id', 'oferta_academica_id']);
    });

    it('validates usuario_id exists', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->post('/matriculas-carrera', [
            'usuario_id' => 99999,
            'oferta_academica_id' => $oferta->id,
        ]);

        $response->assertSessionHasErrors(['usuario_id']);
    });

    it('validates oferta_academica_id exists', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $student = User::factory()->create(['is_estudiante' => true]);

        $response = $this->post('/matriculas-carrera', [
            'usuario_id' => $student->id,
            'oferta_academica_id' => 99999,
        ]);

        $response->assertSessionHasErrors(['oferta_academica_id']);
    });

    it('prevents duplicate enrollment in same career', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $student = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();

        MatriculaCarrera::create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        $response = $this->post('/matriculas-carrera', [
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $response->assertSessionHasErrors(['usuario_id']);
    });

    it('validates estado is a valid enum value', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $matricula = MatriculaCarrera::factory()->create();

        $response = $this->put("/matriculas-carrera/{$matricula->id}", [
            'estado' => 'invalido',
        ]);

        $response->assertSessionHasErrors(['estado']);
    });
});
