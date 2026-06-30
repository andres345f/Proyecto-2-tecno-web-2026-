<?php

use App\Models\OfertaAcademica;
use App\Models\PlanPago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PlanPago CRUD operations', function () {

    // --- Authorization Tests ---

    it('requires authentication to access planes pago index', function () {
        $response = $this->get('/planes-pago');
        $response->assertRedirect('/login');
    });

    it('allows secretaria to access planes pago index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/planes-pago');
        $response->assertOk();
    });

    it('allows director to access planes pago index', function () {
        $user = User::factory()->create(['is_director' => true]);
        $this->actingAs($user);

        $response = $this->get('/planes-pago');
        $response->assertOk();
    });

    it('allows propietario to access planes pago index', function () {
        $user = User::factory()->create(['is_propietario' => true]);
        $this->actingAs($user);

        $response = $this->get('/planes-pago');
        $response->assertOk();
    });

    it('denies student access to planes pago index', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $response = $this->get('/planes-pago');
        $response->assertStatus(403);
    });

    it('denies profesor access to planes pago index', function () {
        $user = User::factory()->create(['is_profesor' => true]);
        $this->actingAs($user);

        $response = $this->get('/planes-pago');
        $response->assertStatus(403);
    });

    // --- Create Tests ---

    it('shows create plan pago form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->get("/planes-pago/create?oferta_academica_id={$oferta->id}");
        $response->assertOk();
    });

    it('creates a new plan pago with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->post('/planes-pago', [
            'oferta_academica_id' => $oferta->id,
            'nombre' => 'Semestral Regular',
            'tipo' => 'por_periodo',
            'monto_matricula' => 50.00,
            'monto_cuota' => 30.00,
            'cantidad_cuotas' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('planes_pago', [
            'oferta_academica_id' => $oferta->id,
            'nombre' => 'Semestral Regular',
            'tipo' => 'por_periodo',
        ]);
    });

    // --- Read Tests ---

    it('shows plan pago details', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $plan = PlanPago::factory()->create();

        $response = $this->get("/planes-pago/{$plan->id}");
        $response->assertOk();
    });

    it('lists planes pago on index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        PlanPago::factory()->count(3)->create();

        $response = $this->get('/planes-pago');
        $response->assertOk();
    });

    // --- Update Tests ---

    it('shows edit plan pago form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $plan = PlanPago::factory()->create();

        $response = $this->get("/planes-pago/{$plan->id}/edit");
        $response->assertOk();
    });

    it('updates a plan pago with valid data', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $plan = PlanPago::factory()->create(['nombre' => 'Old Plan']);

        $response = $this->put("/planes-pago/{$plan->id}", [
            'oferta_academica_id' => $plan->oferta_academica_id,
            'nombre' => 'New Plan',
            'tipo' => $plan->tipo,
            'monto_matricula' => $plan->monto_matricula,
            'monto_cuota' => $plan->monto_cuota,
            'cantidad_cuotas' => $plan->cantidad_cuotas,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('planes_pago', [
            'id' => $plan->id,
            'nombre' => 'New Plan',
        ]);
    });

    // --- Delete Tests ---

    it('deletes a plan pago via soft delete', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $plan = PlanPago::factory()->create();

        $response = $this->delete("/planes-pago/{$plan->id}");
        $response->assertRedirect();
        $this->assertSoftDeleted('planes_pago', ['id' => $plan->id]);
    });

    // --- Validation Tests ---

    it('validates required fields for plan pago creation', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/planes-pago', []);
        $response->assertSessionHasErrors(['oferta_academica_id', 'nombre', 'tipo', 'monto_matricula', 'monto_cuota', 'cantidad_cuotas']);
    });

    it('validates tipo is a valid enum value', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->post('/planes-pago', [
            'oferta_academica_id' => $oferta->id,
            'nombre' => 'Test Plan',
            'tipo' => 'invalido',
            'monto_matricula' => 50,
            'monto_cuota' => 30,
            'cantidad_cuotas' => 5,
        ]);

        $response->assertSessionHasErrors(['tipo']);
    });

    it('validates monto_matricula is numeric and positive', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->post('/planes-pago', [
            'oferta_academica_id' => $oferta->id,
            'nombre' => 'Test Plan',
            'tipo' => 'por_periodo',
            'monto_matricula' => -10,
            'monto_cuota' => 30,
            'cantidad_cuotas' => 5,
        ]);

        $response->assertSessionHasErrors(['monto_matricula']);
    });

    it('validates cantidad_cuotas is a positive integer', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();

        $response = $this->post('/planes-pago', [
            'oferta_academica_id' => $oferta->id,
            'nombre' => 'Test Plan',
            'tipo' => 'por_periodo',
            'monto_matricula' => 50,
            'monto_cuota' => 30,
            'cantidad_cuotas' => 0,
        ]);

        $response->assertSessionHasErrors(['cantidad_cuotas']);
    });

    it('validates oferta_academica_id exists', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/planes-pago', [
            'oferta_academica_id' => 99999,
            'nombre' => 'Test Plan',
            'tipo' => 'por_periodo',
            'monto_matricula' => 50,
            'monto_cuota' => 30,
            'cantidad_cuotas' => 5,
        ]);

        $response->assertSessionHasErrors(['oferta_academica_id']);
    });
});
