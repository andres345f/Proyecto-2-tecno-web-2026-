<?php

use App\Models\Materia;
use App\Models\MallaCurricular;
use App\Models\OfertaAcademica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MallaCurricular operations', function () {

    // --- Authorization Tests ---

    it('requires authentication to add materia to malla', function () {
        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        $response = $this->post("/ofertas-academicas/{$oferta->id}/materias", [
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);
        $response->assertRedirect('/login');
    });

    it('allows secretaria to add materia to malla', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        $response = $this->post("/ofertas-academicas/{$oferta->id}/materias", [
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('malla_curricular', [
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);
    });

    it('denies student access to add materia to malla', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        $response = $this->post("/ofertas-academicas/{$oferta->id}/materias", [
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);

        $response->assertStatus(403);
    });

    // --- Add Materia Tests ---

    it('adds materia to malla with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        $response = $this->post("/ofertas-academicas/{$oferta->id}/materias", [
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('malla_curricular', [
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);
    });

    it('validates materia exists when adding to malla', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->post("/ofertas-academicas/{$oferta->id}/materias", [
            'materia_id' => 99999,
            'semestre_orden' => 1,
        ]);

        $response->assertSessionHasErrors(['materia_id']);
    });

    it('validates oferta academica exists when adding to malla', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $materia = Materia::factory()->create();

        $response = $this->post('/ofertas-academicas/99999/materias', [
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);

        $response->assertStatus(404);
    });

    it('validates semestre_orden is required', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        $response = $this->post("/ofertas-academicas/{$oferta->id}/materias", [
            'materia_id' => $materia->id,
        ]);

        $response->assertSessionHasErrors(['semestre_orden']);
    });

    it('validates semestre_orden is a positive integer', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        $response = $this->post("/ofertas-academicas/{$oferta->id}/materias", [
            'materia_id' => $materia->id,
            'semestre_orden' => 0,
        ]);

        $response->assertSessionHasErrors(['semestre_orden']);
    });

    it('prevents duplicate materia in same malla', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        // First add
        $this->post("/ofertas-academicas/{$oferta->id}/materias", [
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);

        // Try to add same materia again
        $response = $this->post("/ofertas-academicas/{$oferta->id}/materias", [
            'materia_id' => $materia->id,
            'semestre_orden' => 2,
        ]);

        $response->assertSessionHasErrors(['materia_id']);
    });

    it('allows same materia in different ofertas academicas', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta1 = OfertaAcademica::factory()->create();
        $oferta2 = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        // Add to first oferta
        $this->post("/ofertas-academicas/{$oferta1->id}/materias", [
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);

        // Add to second oferta - should succeed
        $response = $this->post("/ofertas-academicas/{$oferta2->id}/materias", [
            'materia_id' => $materia->id,
            'semestre_orden' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('malla_curricular', [
            'oferta_academica_id' => $oferta1->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);
        $this->assertDatabaseHas('malla_curricular', [
            'oferta_academica_id' => $oferta2->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 2,
        ]);
    });

    // --- Remove Materia Tests ---

    it('removes materia from malla', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        // Add first
        $this->post("/ofertas-academicas/{$oferta->id}/materias", [
            'materia_id' => $materia->id,
            'semestre_orden' => 1,
        ]);

        // Remove
        $response = $this->delete("/ofertas-academicas/{$oferta->id}/materias/{$materia->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('malla_curricular', [
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia->id,
        ]);
    });

    it('returns 404 when removing non-existent materia from malla', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        $response = $this->delete("/ofertas-academicas/{$oferta->id}/materias/{$materia->id}");
        $response->assertStatus(404);
    });

    // --- Model Relationship Tests ---

    it('loads materias relationship on oferta academica', function () {
        $oferta = OfertaAcademica::factory()->create();
        $materia1 = Materia::factory()->create();
        $materia2 = Materia::factory()->create();

        $oferta->materias()->attach($materia1->id, ['semestre_orden' => 1]);
        $oferta->materias()->attach($materia2->id, ['semestre_orden' => 2]);

        $oferta->refresh();
        expect($oferta->materias)->toHaveCount(2);
        expect($oferta->materias->first()->pivot->semestre_orden)->toBe(1);
    });

    it('loads ofertas academicas relationship on materia', function () {
        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        $materia->ofertasAcademicas()->attach($oferta->id, ['semestre_orden' => 1]);

        $materia->refresh();
        expect($materia->ofertasAcademicas)->toHaveCount(1);
    });

    it('stores a career-specific prerequisite in malla curricular', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $materia1 = Materia::factory()->create();
        $materia2 = Materia::factory()->create();

        // Attach both to offering's malla first
        $malla1 = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia1->id,
            'semestre_orden' => 1,
        ]);
        $malla2 = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia2->id,
            'semestre_orden' => 2,
        ]);

        $response = $this->post("/ofertas-academicas/{$oferta->id}/materias/{$materia2->id}/prerrequisitos", [
            'prerequisito_id' => $materia1->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('materia_prerequisito', [
            'malla_curricular_id' => $malla2->id,
            'prerequisito_malla_id' => $malla1->id,
        ]);
    });

    it('prevents a materia from being its own prerequisite', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();

        $oferta->materias()->attach($materia->id, ['semestre_orden' => 1]);

        $response = $this->post("/ofertas-academicas/{$oferta->id}/materias/{$materia->id}/prerrequisitos", [
            'prerequisito_id' => $materia->id,
        ]);

        $response->assertSessionHasErrors(['prerequisito_id']);
    });

    it('removes a career-specific prerequisite from malla curricular', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $materia1 = Materia::factory()->create();
        $materia2 = Materia::factory()->create();

        $malla1 = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia1->id,
            'semestre_orden' => 1,
        ]);
        $malla2 = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia2->id,
            'semestre_orden' => 2,
        ]);

        $malla2->prerrequisitos()->attach($malla1->id);

        $response = $this->delete("/ofertas-academicas/{$oferta->id}/materias/{$materia2->id}/prerrequisitos/{$materia1->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('materia_prerequisito', [
            'malla_curricular_id' => $malla2->id,
            'prerequisito_malla_id' => $malla1->id,
        ]);
    });
});
