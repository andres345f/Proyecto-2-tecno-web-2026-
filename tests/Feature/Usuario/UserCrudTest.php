<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('User CRUD operations', function () {

    // --- Authorization Tests ---

    it('requires authentication to access user index', function () {
        $response = $this->get('/usuarios');
        $response->assertRedirect('/login');
    });

    it('allows director to access user index', function () {
        $user = User::factory()->create(['is_director' => true]);
        $this->actingAs($user);

        $response = $this->get('/usuarios');
        $response->assertOk();
    });

    it('allows secretaria to access user index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/usuarios');
        $response->assertOk();
    });

    it('allows propietario to access user index', function () {
        $user = User::factory()->create(['is_propietario' => true]);
        $this->actingAs($user);

        $response = $this->get('/usuarios');
        $response->assertOk();
    });

    it('denies student access to user index', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $response = $this->get('/usuarios');
        $response->assertStatus(403);
    });

    it('denies profesor access to user index', function () {
        $user = User::factory()->create(['is_profesor' => true]);
        $this->actingAs($user);

        $response = $this->get('/usuarios');
        $response->assertStatus(403);
    });

    // --- Create Tests ---

    it('shows create user form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/usuarios/create');
        $response->assertOk();
    });

    it('creates a new user with valid data', function () {
        $admin = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($admin);

        $response = $this->post('/usuarios', [
            'name' => 'Carlos Gomez',
            'email' => 'carlos@gmail.com',
            'password' => 'secret123',
            'is_estudiante' => true,
            'is_activo' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'Carlos Gomez',
            'email' => 'carlos@gmail.com',
            'is_estudiante' => true,
            'is_activo' => true,
        ]);
    });

    // --- Update Tests ---

    it('shows edit user form', function () {
        $admin = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($admin);

        $targetUser = User::factory()->create();

        $response = $this->get("/usuarios/{$targetUser->id}/edit");
        $response->assertOk();
    });

    it('updates a user with valid data', function () {
        $admin = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($admin);

        $targetUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@gmail.com',
            'is_profesor' => false,
        ]);

        $response = $this->put("/usuarios/{$targetUser->id}", [
            'name' => 'New Name',
            'email' => 'new@gmail.com',
            'is_profesor' => true,
            'is_activo' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'New Name',
            'email' => 'new@gmail.com',
            'is_profesor' => true,
        ]);
    });

    // --- Delete Tests ---

    it('deletes a user', function () {
        $admin = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($admin);

        $targetUser = User::factory()->create();

        $response = $this->delete("/usuarios/{$targetUser->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    });

    it('does not allow self deletion', function () {
        $admin = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($admin);

        $response = $this->delete("/usuarios/{$admin->id}");
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    });
});
