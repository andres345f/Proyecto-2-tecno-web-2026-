<?php

use App\Models\Materia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('BuscadorController', function () {

    it('returns JSON results for valid search query', function () {
        $user = User::factory()->create(['is_estudiante' => true, 'name' => 'Carlos Test']);
        Materia::factory()->create(['nombre' => 'Fisica Test', 'codigo' => 'FT-01']);

        $response = $this->actingAs($user)
            ->getJson(route('api.buscador-global').'?q=Test');

        $response->assertOk()
            ->assertJsonStructure([
                'usuarios',
                'materias',
                'cuotas',
            ]);

        $json = $response->json();
        expect($json['usuarios'])->not->toBeEmpty();
        expect($json['materias'])->not->toBeEmpty();
    });

    it('rejects unauthenticated access', function () {
        $response = $this->getJson(route('api.buscador-global').'?q=Test');

        $response->assertUnauthorized();
    });

    it('returns empty results for no match', function () {
        $user = User::factory()->create(['is_estudiante' => true]);

        $response = $this->actingAs($user)
            ->getJson(route('api.buscador-global').'?q=ZZZZNONEXISTENT');

        $response->assertOk()
            ->assertJson([
                'usuarios' => [],
                'materias' => [],
                'cuotas' => [],
            ]);
    });

    it('returns grouped results by type', function () {
        $user = User::factory()->create(['is_estudiante' => true, 'name' => 'Search User']);
        Materia::factory()->create(['nombre' => 'Search Materia']);

        $response = $this->actingAs($user)
            ->getJson(route('api.buscador-global').'?q=Search');

        $response->assertOk();
        $json = $response->json();

        expect($json)->toHaveKeys(['usuarios', 'materias', 'cuotas']);
    });

    it('only returns students in user results', function () {
        User::factory()->create(['name' => 'Director User', 'is_director' => true, 'is_estudiante' => false]);
        User::factory()->create(['name' => 'Student User', 'is_estudiante' => true]);

        $user = User::factory()->create(['is_estudiante' => true]);
        $response = $this->actingAs($user)
            ->getJson(route('api.buscador-global').'?q=User');

        $response->assertOk();
        $json = $response->json();

        expect($json['usuarios'])->toHaveCount(1);
        expect($json['usuarios'][0]['nombre'])->toBe('Student User');
    });
});
