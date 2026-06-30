<?php

use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Visita Model', function () {

    test('visita has correct fillable attributes', function () {
        $visita = new Visita;

        $expected = ['url', 'ip_address', 'user_agent', 'usuario_id'];
        $this->assertEquals($expected, $visita->getFillable());
    });

    test('visita belongs to user relationship', function () {
        $user = User::factory()->create();
        $visita = Visita::factory()->create([
            'usuario_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $visita->usuario);
        $this->assertEquals($user->id, $visita->usuario->id);
    });

    test('visita can be created without user', function () {
        $visita = Visita::factory()->create([
            'usuario_id' => null,
        ]);

        $this->assertNull($visita->usuario_id);
        $this->assertNull($visita->usuario);
    });

    test('visita has correct table name', function () {
        $visita = new Visita;

        $this->assertEquals('visitas', $visita->getTable());
    });

    test('visita factory creates valid record', function () {
        $visita = Visita::factory()->create();

        $this->assertNotNull($visita->url);
        $this->assertDatabaseHas('visitas', [
            'id' => $visita->id,
        ]);
    });
});
