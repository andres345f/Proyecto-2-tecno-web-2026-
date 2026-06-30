<?php

use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

describe('VisitaController', function () {

    test('visita store endpoint exists', function () {
        $this->assertTrue(Route::has('api.visitas.store'));
    });

    test('visita store creates visita record for anonymous user', function () {
        $response = $this->postJson('/api/visitas', [
            'url' => '/dashboard',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('visitas', [
            'url' => '/dashboard',
            'usuario_id' => null,
        ]);
    });

    test('visita store creates visita record for authenticated user', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/visitas', [
                'url' => '/aulas',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('visitas', [
            'url' => '/aulas',
            'usuario_id' => $user->id,
        ]);
    });

    test('visita store captures ip address', function () {
        $response = $this->postJson('/api/visitas', [
            'url' => '/materias',
        ]);

        $response->assertStatus(200);
        $visita = Visita::where('url', '/materias')->first();
        $this->assertNotNull($visita->ip_address);
    });

    test('visita store captures user agent', function () {
        $response = $this->postJson('/api/visitas', [
            'url' => '/grupos',
        ], [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 Test Browser',
        ]);

        $response->assertStatus(200);
        $visita = Visita::where('url', '/grupos')->first();
        $this->assertNotNull($visita->user_agent);
    });

    test('visita store returns success JSON', function () {
        $response = $this->postJson('/api/visitas', [
            'url' => '/test',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    });

    test('visita store validates url is required', function () {
        $response = $this->postJson('/api/visitas', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('url');
    });

    test('visita store validates url is string', function () {
        $response = $this->postJson('/api/visitas', [
            'url' => 123,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('url');
    });

    test('multiple visits to same url in same session are NOT recorded separately', function () {
        $this->postJson('/api/visitas', ['url' => '/dashboard']);
        $this->postJson('/api/visitas', ['url' => '/dashboard']);
        $this->postJson('/api/visitas', ['url' => '/dashboard']);

        $count = Visita::where('url', '/dashboard')->count();
        $this->assertEquals(1, $count);
    });

    test('visits to same url in different sessions are recorded separately', function () {
        $this->postJson('/api/visitas', ['url' => '/dashboard']);
        
        session()->flush();

        $this->postJson('/api/visitas', ['url' => '/dashboard']);

        $count = Visita::where('url', '/dashboard')->count();
        $this->assertEquals(2, $count);
    });
});
