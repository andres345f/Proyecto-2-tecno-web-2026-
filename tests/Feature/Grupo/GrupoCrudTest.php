<?php

use App\Models\Grupo;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Grupo CRUD operations', function () {

    it('requires authentication to access grupos index', function () {
        $response = $this->get('/grupos');
        $response->assertRedirect('/login');
    });

    it('allows secretaria to access grupos index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/grupos');
        $response->assertOk();
    });

    it('allows director to access grupos index', function () {
        $user = User::factory()->create(['is_director' => true]);
        $this->actingAs($user);

        $response = $this->get('/grupos');
        $response->assertOk();
    });

    it('denies student access to grupos index', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $response = $this->get('/grupos');
        $response->assertStatus(403);
    });

    it('shows create grupo form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/grupos/create');
        $response->assertOk();
    });

    it('creates a new grupo with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $materia = Materia::factory()->create();

        $response = $this->post('/grupos', [
            'codigo' => 'SIS-101-G1',
            'materia_id' => $materia->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('grupos', [
            'codigo' => 'SIS-101-G1',
            'materia_id' => $materia->id,
        ]);
    });

    it('shows edit grupo form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $grupo = Grupo::factory()->create();

        $response = $this->get("/grupos/{$grupo->id}/edit");
        $response->assertOk();
    });

    it('updates a grupo with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $grupo = Grupo::factory()->create(['codigo' => 'SIS-OLD']);
        $materia = Materia::factory()->create();

        $response = $this->put("/grupos/{$grupo->id}", [
            'codigo' => 'SIS-NEW',
            'materia_id' => $materia->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('grupos', [
            'id' => $grupo->id,
            'codigo' => 'SIS-NEW',
            'materia_id' => $materia->id,
        ]);
    });

    it('deletes a grupo via soft delete', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $grupo = Grupo::factory()->create();

        $response = $this->delete("/grupos/{$grupo->id}");
        $response->assertRedirect();
        
        $this->assertSoftDeleted('grupos', [
            'id' => $grupo->id,
        ]);
    });

    it('validates required fields for grupo creation', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/grupos', []);
        $response->assertSessionHasErrors(['codigo', 'materia_id']);
    });
});
