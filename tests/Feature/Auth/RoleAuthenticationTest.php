<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('Role-based authentication', function () {

    it('adds role columns to users table via migration', function () {
        $user = User::factory()->create([
            'is_propietario' => true,
            'is_director' => false,
            'is_secretaria' => false,
            'is_profesor' => false,
            'is_estudiante' => false,
            'is_activo' => true,
        ]);

        expect($user->is_propietario)->toBeTrue()
            ->and($user->is_director)->toBeFalse()
            ->and($user->is_secretaria)->toBeFalse()
            ->and($user->is_profesor)->toBeFalse()
            ->and($user->is_estudiante)->toBeFalse()
            ->and($user->is_activo)->toBeTrue();
    });

    it('allows login for propietario user', function () {
        $user = User::factory()->create([
            'email' => 'propietario@test.com',
            'password' => Hash::make('password'),
            'is_propietario' => true,
            'is_activo' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'propietario@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    });

    it('allows login for director user', function () {
        $user = User::factory()->create([
            'email' => 'director@test.com',
            'password' => Hash::make('password'),
            'is_director' => true,
            'is_activo' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'director@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    });

    it('allows login for secretaria user', function () {
        $user = User::factory()->create([
            'email' => 'secretaria@test.com',
            'password' => Hash::make('password'),
            'is_secretaria' => true,
            'is_activo' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'secretaria@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    });

    it('allows login for profesor user', function () {
        $user = User::factory()->create([
            'email' => 'profesor@test.com',
            'password' => Hash::make('password'),
            'is_profesor' => true,
            'is_activo' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'profesor@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    });

    it('allows login for estudiante user', function () {
        $user = User::factory()->create([
            'email' => 'estudiante@test.com',
            'password' => Hash::make('password'),
            'is_estudiante' => true,
            'is_activo' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'estudiante@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    });

    it('prevents inactive user from logging in', function () {
        $user = User::factory()->create([
            'email' => 'inactive@test.com',
            'password' => Hash::make('password'),
            'is_estudiante' => true,
            'is_activo' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@test.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    });

    it('redirects to dashboard based on user role', function () {
        $user = User::factory()->create([
            'email' => 'propietario@test.com',
            'password' => Hash::make('password'),
            'is_propietario' => true,
            'is_activo' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'propietario@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
    });

    it('shows role-specific content on dashboard', function () {
        $user = User::factory()->create([
            'email' => 'propietario@test.com',
            'password' => Hash::make('password'),
            'is_propietario' => true,
            'is_activo' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertOk();
    });

    it('has role accessor methods on User model', function () {
        $user = User::factory()->create([
            'is_propietario' => true,
            'is_director' => false,
            'is_secretaria' => false,
            'is_profesor' => false,
            'is_estudiante' => false,
            'is_activo' => true,
        ]);

        expect($user->is_propietario())->toBeTrue()
            ->and($user->is_director())->toBeFalse()
            ->and($user->is_secretaria())->toBeFalse()
            ->and($user->is_profesor())->toBeFalse()
            ->and($user->is_estudiante())->toBeFalse()
            ->and($user->is_active())->toBeTrue();
    });

    it('has hasRole method on User model', function () {
        $user = User::factory()->create([
            'is_propietario' => true,
            'is_director' => false,
            'is_secretaria' => false,
            'is_profesor' => false,
            'is_estudiante' => false,
            'is_activo' => true,
        ]);

        expect($user->hasRole('propietario'))->toBeTrue()
            ->and($user->hasRole('director'))->toBeFalse()
            ->and($user->hasRole('secretaria'))->toBeFalse()
            ->and($user->hasRole('profesor'))->toBeFalse()
            ->and($user->hasRole('estudiante'))->toBeFalse();
    });

    it('has getPrimaryRole method on User model', function () {
        $user = User::factory()->create([
            'is_propietario' => true,
            'is_director' => false,
            'is_secretaria' => false,
            'is_profesor' => false,
            'is_estudiante' => false,
            'is_activo' => true,
        ]);

        expect($user->getPrimaryRole())->toBe('propietario');
    });

    it('has getRoles method on User model', function () {
        $user = User::factory()->create([
            'is_propietario' => false,
            'is_director' => true,
            'is_secretaria' => false,
            'is_profesor' => true,
            'is_estudiante' => false,
            'is_activo' => true,
        ]);

        $roles = $user->getRoles();

        expect($roles)->toContain('director')
            ->and($roles)->toContain('profesor')
            ->and($roles)->not->toContain('propietario')
            ->and($roles)->not->toContain('secretaria')
            ->and($roles)->not->toContain('estudiante');
    });

    it('has hasMultipleRoles method on User model', function () {
        $user = User::factory()->create([
            'is_propietario' => false,
            'is_director' => true,
            'is_secretaria' => false,
            'is_profesor' => true,
            'is_estudiante' => false,
            'is_activo' => true,
        ]);

        expect($user->hasMultipleRoles())->toBeTrue();

        $user2 = User::factory()->create([
            'is_propietario' => true,
            'is_director' => false,
            'is_secretaria' => false,
            'is_profesor' => false,
            'is_estudiante' => false,
            'is_activo' => true,
        ]);

        expect($user2->hasMultipleRoles())->toBeFalse();
    });
});
