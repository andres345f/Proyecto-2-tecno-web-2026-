<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

describe('Role-based Dashboard', function () {

    it('shows propietario dashboard for propietario user', function () {
        $user = User::factory()->create([
            'is_propietario' => true,
            'is_activo' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Dashboard')
            ->has('user')
            ->has('primaryRole')
            ->has('roles')
        );
    });

    it('shows director dashboard for director user', function () {
        $user = User::factory()->create([
            'is_director' => true,
            'is_activo' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Dashboard')
            ->has('user')
            ->has('primaryRole')
            ->has('roles')
        );
    });

    it('shows secretaria dashboard for secretaria user', function () {
        $user = User::factory()->create([
            'is_secretaria' => true,
            'is_activo' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Dashboard')
            ->has('user')
            ->has('primaryRole')
            ->has('roles')
        );
    });

    it('shows profesor dashboard for profesor user', function () {
        $user = User::factory()->create([
            'is_profesor' => true,
            'is_activo' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Dashboard')
            ->has('user')
            ->has('primaryRole')
            ->has('roles')
        );
    });

    it('shows estudiante dashboard for estudiante user', function () {
        $user = User::factory()->create([
            'is_estudiante' => true,
            'is_activo' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Dashboard')
            ->has('user')
            ->has('primaryRole')
            ->has('roles')
        );
    });

    it('passes correct user data to dashboard', function () {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_propietario' => true,
            'is_activo' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Dashboard')
            ->where('user.name', 'Test User')
            ->where('user.email', 'test@example.com')
            ->where('primaryRole', 'propietario')
            ->where('roles', ['propietario'])
        );
    });

    it('passes multiple roles to dashboard', function () {
        $user = User::factory()->create([
            'is_profesor' => true,
            'is_director' => true,
            'is_activo' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Dashboard')
            ->where('primaryRole', 'director')
            ->where('roles', ['director', 'profesor'])
        );
    });

    it('redirects unauthenticated users to login', function () {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    });

    it('denies access for inactive user', function () {
        $user = User::factory()->create([
            'is_estudiante' => true,
            'is_activo' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    });
});
