<?php

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

describe('Role Middleware', function () {

    it('allows access for user with required role', function () {
        $user = User::factory()->create([
            'is_propietario' => true,
            'is_activo' => true,
        ]);

        Route::middleware(['auth', RoleMiddleware::class.':propietario'])->get('/test-role', function () {
            return response('OK');
        });

        $response = $this->actingAs($user)->get('/test-role');

        $response->assertOk();
        $response->assertSee('OK');
    });

    it('denies access for user without required role', function () {
        $user = User::factory()->create([
            'is_estudiante' => true,
            'is_activo' => true,
        ]);

        Route::middleware(['auth', RoleMiddleware::class.':propietario'])->get('/test-role', function () {
            return response('OK');
        });

        $response = $this->actingAs($user)->get('/test-role');

        $response->assertForbidden();
    });

    it('allows access for user with any of multiple roles', function () {
        $user = User::factory()->create([
            'is_director' => true,
            'is_activo' => true,
        ]);

        Route::middleware(['auth', RoleMiddleware::class.':propietario,director'])->get('/test-role', function () {
            return response('OK');
        });

        $response = $this->actingAs($user)->get('/test-role');

        $response->assertOk();
    });

    it('denies access for user with none of multiple roles', function () {
        $user = User::factory()->create([
            'is_estudiante' => true,
            'is_activo' => true,
        ]);

        Route::middleware(['auth', RoleMiddleware::class.':propietario,director'])->get('/test-role', function () {
            return response('OK');
        });

        $response = $this->actingAs($user)->get('/test-role');

        $response->assertForbidden();
    });

    it('redirects unauthenticated users to login', function () {
        Route::middleware(['auth', RoleMiddleware::class.':propietario'])->get('/test-role', function () {
            return response('OK');
        });

        $response = $this->get('/test-role');

        $response->assertRedirect('/login');
    });

    it('denies access for inactive user even with correct role', function () {
        $user = User::factory()->create([
            'is_propietario' => true,
            'is_activo' => false,
        ]);

        Route::middleware(['auth', RoleMiddleware::class.':propietario'])->get('/test-role', function () {
            return response('OK');
        });

        $response = $this->actingAs($user)->get('/test-role');

        $response->assertRedirect('/login');
    });
});
