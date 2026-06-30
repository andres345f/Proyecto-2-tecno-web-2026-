<?php

use App\Models\Aula;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Aula CRUD operations', function () {

    // --- Authorization Tests ---

    it('requires authentication to access aulas index', function () {
        $response = $this->get('/aulas');
        $response->assertRedirect('/login');
    });

    it('allows secretaria to access aulas index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/aulas');
        $response->assertOk();
    });

    it('allows director to access aulas index', function () {
        $user = User::factory()->create(['is_director' => true]);
        $this->actingAs($user);

        $response = $this->get('/aulas');
        $response->assertOk();
    });

    it('allows propietario to access aulas index', function () {
        $user = User::factory()->create(['is_propietario' => true]);
        $this->actingAs($user);

        $response = $this->get('/aulas');
        $response->assertOk();
    });

    it('denies student access to aulas index', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $response = $this->get('/aulas');
        $response->assertStatus(403);
    });

    it('denies profesor access to aulas index', function () {
        $user = User::factory()->create(['is_profesor' => true]);
        $this->actingAs($user);

        $response = $this->get('/aulas');
        $response->assertStatus(403);
    });

    // --- Create Tests ---

    it('shows create aula form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/aulas/create');
        $response->assertOk();
    });

    it('creates a new aula with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/aulas', [
            'nombre' => 'Laboratorio 101',
            'codigo' => 'LAB-101',
            'capacidad' => 30,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('aulas', [
            'nombre' => 'Laboratorio 101',
            'codigo' => 'LAB-101',
            'capacidad' => 30,
        ]);
    });

    // --- Read Tests ---

    it('shows aula details', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $aula = Aula::factory()->create(['nombre' => 'Aula 202']);

        $response = $this->get("/aulas/{$aula->id}");
        $response->assertOk();
    });

    it('lists all aulas on index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        Aula::factory()->count(3)->create();

        $response = $this->get('/aulas');
        $response->assertOk();
    });

    // --- Update Tests ---

    it('shows edit aula form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $aula = Aula::factory()->create();

        $response = $this->get("/aulas/{$aula->id}/edit");
        $response->assertOk();
    });

    it('updates an aula with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $aula = Aula::factory()->create(['nombre' => 'Old Name']);

        $response = $this->put("/aulas/{$aula->id}", [
            'nombre' => 'New Name',
            'codigo' => $aula->codigo,
            'capacidad' => 50,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('aulas', [
            'id' => $aula->id,
            'nombre' => 'New Name',
            'capacidad' => 50,
        ]);
    });

    // --- Delete Tests ---

    it('deletes an aula via soft delete', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $aula = Aula::factory()->create();

        $response = $this->delete("/aulas/{$aula->id}");
        $response->assertRedirect();
        $this->assertSoftDeleted('aulas', ['id' => $aula->id]);
    });

    // --- Validation Tests ---

    it('validates required fields for aula creation', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/aulas', []);
        $response->assertSessionHasErrors(['nombre', 'codigo', 'capacidad']);
    });

    it('validates unique codigo for aula', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        Aula::factory()->create(['codigo' => 'LAB-101']);

        $response = $this->post('/aulas', [
            'nombre' => 'Another Lab',
            'codigo' => 'LAB-101',
            'capacidad' => 25,
        ]);

        $response->assertSessionHasErrors(['codigo']);
    });

    it('validates capacidad is a positive integer', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/aulas', [
            'nombre' => 'Lab',
            'codigo' => 'LAB-NEW',
            'capacidad' => 0,
        ]);

        $response->assertSessionHasErrors(['capacidad']);
    });

    it('allows same codigo when updating the same aula', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $aula = Aula::factory()->create(['codigo' => 'LAB-101']);

        $response = $this->put("/aulas/{$aula->id}", [
            'nombre' => 'Updated Name',
            'codigo' => 'LAB-101',
            'capacidad' => 40,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('aulas', [
            'id' => $aula->id,
            'nombre' => 'Updated Name',
        ]);
    });

    it('validates unique codigo on update excluding current record', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $aula1 = Aula::factory()->create(['codigo' => 'LAB-101']);
        $aula2 = Aula::factory()->create(['codigo' => 'LAB-102']);

        $response = $this->put("/aulas/{$aula2->id}", [
            'nombre' => 'Updated',
            'codigo' => 'LAB-101',
            'capacidad' => 25,
        ]);

        $response->assertSessionHasErrors(['codigo']);
    });
});
