<?php

use App\Models\OfertaAcademica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('OfertaAcademica CRUD operations', function () {

    // --- Authorization Tests ---

    it('requires authentication to access ofertas academicas index', function () {
        $response = $this->get('/ofertas-academicas');
        $response->assertRedirect('/login');
    });

    it('allows secretaria to access ofertas academicas index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/ofertas-academicas');
        $response->assertOk();
    });

    it('allows director to access ofertas academicas index', function () {
        $user = User::factory()->create(['is_director' => true]);
        $this->actingAs($user);

        $response = $this->get('/ofertas-academicas');
        $response->assertOk();
    });

    it('allows propietario to access ofertas academicas index', function () {
        $user = User::factory()->create(['is_propietario' => true]);
        $this->actingAs($user);

        $response = $this->get('/ofertas-academicas');
        $response->assertOk();
    });

    it('denies student access to ofertas academicas index', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $response = $this->get('/ofertas-academicas');
        $response->assertStatus(403);
    });

    it('denies profesor access to ofertas academicas index', function () {
        $user = User::factory()->create(['is_profesor' => true]);
        $this->actingAs($user);

        $response = $this->get('/ofertas-academicas');
        $response->assertStatus(403);
    });

    // --- Create Tests ---

    it('shows create oferta academica form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/ofertas-academicas/create');
        $response->assertOk();
    });

    it('creates a new oferta academica with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/ofertas-academicas', [
            'nombre' => 'Técnico en Redes',
            'codigo' => 'TEC-RED',
            'descripcion' => 'Carrera técnica en redes de computadoras',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ofertas_academicas', [
            'nombre' => 'Técnico en Redes',
            'codigo' => 'TEC-RED',
            'descripcion' => 'Carrera técnica en redes de computadoras',
        ]);
    });

    // --- Read Tests ---

    it('shows oferta academica details', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create(['nombre' => 'Ingeniería de Sistemas']);

        $response = $this->get("/ofertas-academicas/{$oferta->id}");
        $response->assertOk();
    });

    it('lists all ofertas academicas on index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        OfertaAcademica::factory()->count(3)->create();

        $response = $this->get('/ofertas-academicas');
        $response->assertOk();
    });

    // --- Update Tests ---

    it('shows edit oferta academica form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->get("/ofertas-academicas/{$oferta->id}/edit");
        $response->assertOk();
    });

    it('updates an oferta academica with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create(['nombre' => 'Old Name']);

        $response = $this->put("/ofertas-academicas/{$oferta->id}", [
            'nombre' => 'New Name',
            'codigo' => $oferta->codigo,
            'descripcion' => 'Updated description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ofertas_academicas', [
            'id' => $oferta->id,
            'nombre' => 'New Name',
            'descripcion' => 'Updated description',
        ]);
    });

    // --- Delete Tests ---

    it('deletes an oferta academica via soft delete', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->delete("/ofertas-academicas/{$oferta->id}");
        $response->assertRedirect();
        $this->assertSoftDeleted('ofertas_academicas', ['id' => $oferta->id]);
    });

    // --- Validation Tests ---

    it('validates required fields for oferta academica creation', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/ofertas-academicas', []);
        $response->assertSessionHasErrors(['nombre', 'codigo']);
    });

    it('validates unique codigo for oferta academica', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        OfertaAcademica::factory()->create(['codigo' => 'TEC-RED']);

        $response = $this->post('/ofertas-academicas', [
            'nombre' => 'Another Career',
            'codigo' => 'TEC-RED',
            'descripcion' => 'Description',
        ]);

        $response->assertSessionHasErrors(['codigo']);
    });

    it('allows same codigo when updating the same oferta academica', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create(['codigo' => 'TEC-RED']);

        $response = $this->put("/ofertas-academicas/{$oferta->id}", [
            'nombre' => 'Updated Name',
            'codigo' => 'TEC-RED',
            'descripcion' => 'Description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ofertas_academicas', [
            'id' => $oferta->id,
            'nombre' => 'Updated Name',
        ]);
    });

    it('validates unique codigo on update excluding current record', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta1 = OfertaAcademica::factory()->create(['codigo' => 'TEC-RED']);
        $oferta2 = OfertaAcademica::factory()->create(['codigo' => 'ING-SIS']);

        $response = $this->put("/ofertas-academicas/{$oferta2->id}", [
            'nombre' => 'Updated',
            'codigo' => 'TEC-RED',
            'descripcion' => 'Description',
        ]);

        $response->assertSessionHasErrors(['codigo']);
    });
});
