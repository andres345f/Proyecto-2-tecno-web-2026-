<?php

use App\Models\Aula;
use App\Models\Grupo;
use App\Models\GrupoPeriodo;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Horario CRUD operations', function () {

    // --- Authorization Tests ---

    it('requires authentication to access horarios index', function () {
        $response = $this->get('/horarios');
        $response->assertRedirect('/login');
    });

    it('allows secretaria to access horarios index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/horarios');
        $response->assertOk();
    });

    it('allows director to access horarios index', function () {
        $user = User::factory()->create(['is_director' => true]);
        $this->actingAs($user);

        $response = $this->get('/horarios');
        $response->assertOk();
    });

    it('denies student access to horarios index', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $response = $this->get('/horarios');
        $response->assertStatus(403);
    });

    // --- Create Tests ---

    it('shows create horario form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/horarios/create');
        $response->assertOk();
    });

    it('creates a new horario with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $aula = Aula::factory()->create();
        $grupoPeriodo = GrupoPeriodo::factory()->create();

        $response = $this->post('/horarios', [
            'dia' => 'Lunes',
            'hora_inicio' => '08:00',
            'hora_fin' => '09:30',
            'aula_id' => $aula->id,
            'grupo_periodo_id' => $grupoPeriodo->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('horarios', [
            'dia' => 'Lunes',
            'hora_inicio' => '08:00',
            'hora_fin' => '09:30',
            'aula_id' => $aula->id,
            'grupo_periodo_id' => $grupoPeriodo->id,
        ]);
    });

    // --- Read Tests ---

    it('shows horario details', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $horario = Horario::factory()->create();

        $response = $this->get("/horarios/{$horario->id}");
        $response->assertOk();
    });

    it('lists all horarios on index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        Horario::factory()->count(3)->create();

        $response = $this->get('/horarios');
        $response->assertOk();
    });

    // --- Update Tests ---

    it('shows edit horario form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $horario = Horario::factory()->create();

        $response = $this->get("/horarios/{$horario->id}/edit");
        $response->assertOk();
    });

    it('updates a horario with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $horario = Horario::factory()->create(['dia' => 'Lunes']);
        $aula = Aula::factory()->create();
        $grupoPeriodo = GrupoPeriodo::factory()->create();

        $response = $this->put("/horarios/{$horario->id}", [
            'dia' => 'Martes',
            'hora_inicio' => '10:00',
            'hora_fin' => '11:30',
            'aula_id' => $aula->id,
            'grupo_periodo_id' => $grupoPeriodo->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('horarios', [
            'id' => $horario->id,
            'dia' => 'Martes',
            'hora_inicio' => '10:00',
            'hora_fin' => '11:30',
            'aula_id' => $aula->id,
            'grupo_periodo_id' => $grupoPeriodo->id,
        ]);
    });

    // --- Delete Tests ---

    it('deletes a horario', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $horario = Horario::factory()->create();

        $response = $this->delete("/horarios/{$horario->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('horarios', ['id' => $horario->id]);
    });

    // --- Validation Tests ---

    it('validates required fields for horario creation', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/horarios', []);
        $response->assertSessionHasErrors(['dia', 'hora_inicio', 'hora_fin']);
    });

    it('validates dia is in allowed list', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $aula = Aula::factory()->create();

        $response = $this->post('/horarios', [
            'dia' => 'InvalidDay',
            'hora_inicio' => '08:00',
            'hora_fin' => '09:30',
            'aula_id' => $aula->id,
        ]);

        $response->assertSessionHasErrors(['dia']);
    });

    it('validates hora_inicio format', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $aula = Aula::factory()->create();

        $response = $this->post('/horarios', [
            'dia' => 'Lunes',
            'hora_inicio' => 'invalid-time',
            'hora_fin' => '09:30',
            'aula_id' => $aula->id,
        ]);

        $response->assertSessionHasErrors(['hora_inicio']);
    });

    it('validates hora_fin is after hora_inicio', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $aula = Aula::factory()->create();

        $response = $this->post('/horarios', [
            'dia' => 'Lunes',
            'hora_inicio' => '09:30',
            'hora_fin' => '08:00',
            'aula_id' => $aula->id,
        ]);

        $response->assertSessionHasErrors(['hora_fin']);
    });

    it('validates hora_fin is after hora_inicio with equal times', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $aula = Aula::factory()->create();

        $response = $this->post('/horarios', [
            'dia' => 'Lunes',
            'hora_inicio' => '08:00',
            'hora_fin' => '08:00',
            'aula_id' => $aula->id,
        ]);

        $response->assertSessionHasErrors(['hora_fin']);
    });

    it('validates aula exists', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/horarios', [
            'dia' => 'Lunes',
            'hora_inicio' => '08:00',
            'hora_fin' => '09:30',
            'aula_id' => 99999,
        ]);

        $response->assertSessionHasErrors(['aula_id']);
    });

    // --- Model Tests ---

    it('loads aula relationship on horario', function () {
        $aula = Aula::factory()->create(['nombre' => 'Lab 101']);
        $horario = Horario::factory()->create(['aula_id' => $aula->id]);

        expect($horario->aula->nombre)->toBe('Lab 101');
    });

    it('loads horarios relationship on aula', function () {
        $aula = Aula::factory()->create();
        Horario::factory()->count(3)->create(['aula_id' => $aula->id]);

        $aula->refresh();
        expect($aula->horarios)->toHaveCount(3);
    });

    it('rejects creation if aula is already occupied at that time', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $aula = Aula::factory()->create();

        // Existente: Lunes 08:00 - 09:30
        Horario::factory()->create([
            'dia' => 'Lunes',
            'hora_inicio' => '08:00',
            'hora_fin' => '09:30',
            'aula_id' => $aula->id,
        ]);

        // Intentar crear solapado: Lunes 09:00 - 10:30
        $response = $this->post('/horarios', [
            'dia' => 'Lunes',
            'hora_inicio' => '09:00',
            'hora_fin' => '10:30',
            'aula_id' => $aula->id,
        ]);

        $response->assertSessionHasErrors(['aula_id']);
    });

    it('rejects creation if docente of the group is already teaching at that time', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $docente = User::factory()->create(['is_profesor' => true]);
        $aula1 = Aula::factory()->create();
        $aula2 = Aula::factory()->create();

        $grupoPeriodo1 = GrupoPeriodo::factory()->create(['docente_id' => $docente->id]);
        $grupoPeriodo2 = GrupoPeriodo::factory()->create(['docente_id' => $docente->id]);

        // Clase existente para docente en grupoPeriodo1: Martes 14:00 - 15:30 en aula1
        Horario::factory()->create([
            'dia' => 'Martes',
            'hora_inicio' => '14:00',
            'hora_fin' => '15:30',
            'aula_id' => $aula1->id,
            'grupo_periodo_id' => $grupoPeriodo1->id,
        ]);

        // Intentar crear clase para el mismo docente en grupoPeriodo2: Martes 15:00 - 16:30 en aula2 (Choque de Docente)
        $response = $this->post('/horarios', [
            'dia' => 'Martes',
            'hora_inicio' => '15:00',
            'hora_fin' => '16:30',
            'aula_id' => $aula2->id,
            'grupo_periodo_id' => $grupoPeriodo2->id,
        ]);

        $response->assertSessionHasErrors(['grupo_periodo_id']);
    });

    it('allows creation if there is no conflict', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $docente = User::factory()->create(['is_profesor' => true]);
        $aula = Aula::factory()->create();
        $grupoPeriodo = GrupoPeriodo::factory()->create(['docente_id' => $docente->id]);

        // Existente: Lunes 08:00 - 09:30
        Horario::factory()->create([
            'dia' => 'Lunes',
            'hora_inicio' => '08:00',
            'hora_fin' => '09:30',
            'aula_id' => $aula->id,
            'grupo_periodo_id' => $grupoPeriodo->id,
        ]);

        // Nuevo: Lunes 09:30 - 11:00 (Misma aula y docente, pero no se solapan)
        $response = $this->post('/horarios', [
            'dia' => 'Lunes',
            'hora_inicio' => '09:30',
            'hora_fin' => '11:00',
            'aula_id' => $aula->id,
            'grupo_periodo_id' => $grupoPeriodo->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('horarios', [
            'dia' => 'Lunes',
            'hora_inicio' => '09:30',
            'hora_fin' => '11:00',
            'aula_id' => $aula->id,
            'grupo_periodo_id' => $grupoPeriodo->id,
        ]);
    });
});
