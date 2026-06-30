<?php

use App\Models\Materia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Materia CRUD operations', function () {

    // --- Authorization Tests ---

    it('requires authentication to access materias index', function () {
        $response = $this->get('/materias');
        $response->assertRedirect('/login');
    });

    it('allows secretaria to access materias index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/materias');
        $response->assertOk();
    });

    it('allows director to access materias index', function () {
        $user = User::factory()->create(['is_director' => true]);
        $this->actingAs($user);

        $response = $this->get('/materias');
        $response->assertOk();
    });

    it('allows propietario to access materias index', function () {
        $user = User::factory()->create(['is_propietario' => true]);
        $this->actingAs($user);

        $response = $this->get('/materias');
        $response->assertOk();
    });

    it('denies student access to materias index', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $response = $this->get('/materias');
        $response->assertStatus(403);
    });

    it('denies profesor access to materias index', function () {
        $user = User::factory()->create(['is_profesor' => true]);
        $this->actingAs($user);

        $response = $this->get('/materias');
        $response->assertStatus(403);
    });

    // --- Create Tests ---

    it('shows create materia form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/materias/create');
        $response->assertOk();
    });

    it('creates a new materia with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/materias', [
            'nombre' => 'Física Aplicada',
            'codigo' => 'FIS-101',
            'descripcion' => 'Curso de física aplicada',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('materias', [
            'nombre' => 'Física Aplicada',
            'codigo' => 'FIS-101',
            'descripcion' => 'Curso de física aplicada',
        ]);
    });

    // --- Read Tests ---

    it('shows materia details', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $materia = Materia::factory()->create(['nombre' => 'Matemáticas']);

        $response = $this->get("/materias/{$materia->id}");
        $response->assertOk();
    });

    it('lists all materias on index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        Materia::factory()->count(3)->create();

        $response = $this->get('/materias');
        $response->assertOk();
    });

    // --- Update Tests ---

    it('shows edit materia form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $materia = Materia::factory()->create();

        $response = $this->get("/materias/{$materia->id}/edit");
        $response->assertOk();
    });

    it('updates a materia with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $materia = Materia::factory()->create(['nombre' => 'Old Name']);

        $response = $this->put("/materias/{$materia->id}", [
            'nombre' => 'New Name',
            'codigo' => $materia->codigo,
            'descripcion' => 'Updated description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('materias', [
            'id' => $materia->id,
            'nombre' => 'New Name',
        ]);
    });

    // --- Delete Tests ---

    it('deletes a materia via soft delete', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $materia = Materia::factory()->create();

        $response = $this->delete("/materias/{$materia->id}");
        $response->assertRedirect();
        $this->assertSoftDeleted('materias', ['id' => $materia->id]);
    });

    // --- Validation Tests ---

    it('validates required fields for materia creation', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/materias', []);
        $response->assertSessionHasErrors(['nombre', 'codigo']);
    });

    it('validates unique codigo for materia', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        Materia::factory()->create(['codigo' => 'FIS-101']);

        $response = $this->post('/materias', [
            'nombre' => 'Another Physics',
            'codigo' => 'FIS-101',
        ]);

        $response->assertSessionHasErrors(['codigo']);
    });

    it('allows same codigo when updating the same materia', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $materia = Materia::factory()->create(['codigo' => 'MAT-101']);

        $response = $this->put("/materias/{$materia->id}", [
            'nombre' => 'Updated Name',
            'codigo' => 'MAT-101',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('materias', [
            'id' => $materia->id,
            'nombre' => 'Updated Name',
        ]);
    });

    it('validates unique codigo on update excluding current record', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $materia1 = Materia::factory()->create(['codigo' => 'MAT-101']);
        $materia2 = Materia::factory()->create(['codigo' => 'MAT-102']);

        $response = $this->put("/materias/{$materia2->id}", [
            'nombre' => 'Updated',
            'codigo' => 'MAT-101',
        ]);

        $response->assertSessionHasErrors(['codigo']);
    });
});
