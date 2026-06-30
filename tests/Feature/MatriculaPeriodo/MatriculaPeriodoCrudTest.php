<?php

use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\OfertaAcademica;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('MatriculaPeriodo CRUD operations', function () {

    // --- Authorization Tests ---

    it('requires authentication to access matriculas periodo index', function () {
        $response = $this->get('/matriculas-periodo');
        $response->assertRedirect('/login');
    });

    it('allows secretaria to access matriculas periodo index', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->get('/matriculas-periodo');
        $response->assertOk();
    });

    it('allows student access to matriculas periodo index and filters by own student', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $response = $this->get('/matriculas-periodo');
        $response->assertOk();
    });

    // --- Create Tests ---

    it('shows create matricula periodo form', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $matriculaCarrera = MatriculaCarrera::factory()->create();

        $response = $this->get("/matriculas-periodo/create?matricula_carrera_id={$matriculaCarrera->id}");
        $response->assertOk();
    });

    it('creates matricula periodo and generates cuotas', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $student = User::factory()->create(['is_estudiante' => true]);
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'monto_matricula' => 50.00,
            'monto_cuota' => 30.00,
            'cantidad_cuotas' => 5,
        ]);

        $response = $this->post('/matriculas-periodo', [
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
        ]);

        $response->assertRedirect();

        $matriculaPeriodo = MatriculaPeriodo::where('matricula_carrera_id', $matriculaCarrera->id)->first();
        $this->assertNotNull($matriculaPeriodo);

        // Should generate 6 cuotas (1 matrícula + 5 cuotas)
        $this->assertDatabaseCount('cuotas', 6);
    });

    // --- Read Tests ---

    it('shows matricula periodo details with cuotas', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $matricula = MatriculaPeriodo::factory()->create();

        $response = $this->get("/matriculas-periodo/{$matricula->id}");
        $response->assertOk();
    });

    // --- Update Tests ---

    it('updates matricula periodo estado', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $matricula = MatriculaPeriodo::factory()->create(['estado' => 'activo']);

        $response = $this->put("/matriculas-periodo/{$matricula->id}", [
            'estado' => 'completado',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('matriculas_periodo', [
            'id' => $matricula->id,
            'estado' => 'completado',
        ]);
    });

    // --- Delete Tests ---

    it('deletes a matricula periodo', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $matricula = MatriculaPeriodo::factory()->create();

        $response = $this->delete("/matriculas-periodo/{$matricula->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('matriculas_periodo', ['id' => $matricula->id]);
    });

    // --- Validation Tests ---

    it('validates required fields for matricula periodo creation', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $response = $this->post('/matriculas-periodo', []);
        $response->assertSessionHasErrors(['matricula_carrera_id', 'periodo_academico_id', 'plan_pago_id']);
    });

    it('validates period belongs to same career as matricula', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $ofertaTecRed = OfertaAcademica::factory()->create();
        $ofertaIngSis = OfertaAcademica::factory()->create();

        $student = User::factory()->create(['is_estudiante' => true]);
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $ofertaTecRed->id,
        ]);

        // Period belongs to different career
        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $ofertaIngSis->id]);
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $ofertaTecRed->id]);

        $response = $this->post('/matriculas-periodo', [
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
        ]);

        $response->assertSessionHasErrors(['periodo_academico_id']);
    });

    it('validates plan belongs to same career as matricula', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $ofertaTecRed = OfertaAcademica::factory()->create();
        $ofertaIngSis = OfertaAcademica::factory()->create();

        $student = User::factory()->create(['is_estudiante' => true]);
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $ofertaTecRed->id,
        ]);

        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $ofertaTecRed->id]);
        // Plan belongs to different career
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $ofertaIngSis->id]);

        $response = $this->post('/matriculas-periodo', [
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
        ]);

        $response->assertSessionHasErrors(['plan_pago_id']);
    });

    it('prevents duplicate active enrollment in same period', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $student = User::factory()->create(['is_estudiante' => true]);
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

        // Create first enrollment
        MatriculaPeriodo::create([
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        // Try to create duplicate
        $response = $this->post('/matriculas-periodo', [
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
        ]);

        $response->assertSessionHasErrors(['periodo_academico_id']);
    });

    it('validates estado is a valid enum value', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $matricula = MatriculaPeriodo::factory()->create();

        $response = $this->put("/matriculas-periodo/{$matricula->id}", [
            'estado' => 'invalido',
        ]);

        $response->assertSessionHasErrors(['estado']);
    });

    it('denies student from enrolling another student', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $otherStudent = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $otherStudent->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'tipo' => 'por_periodo',
        ]);

        $response = $this->post('/matriculas-periodo', [
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
        ]);

        $response->assertSessionHasErrors(['matricula_carrera_id']);
    });

    it('denies student from choosing a special plan', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $user->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $periodo = PeriodoAcademico::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'fecha_inicio_inscripcion' => now()->subDay()->toDateString(),
            'fecha_fin_inscripcion' => now()->addDay()->toDateString(),
            'estado' => 'inscripcion',
        ]);
        $plan = PlanPago::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'tipo' => 'especial',
        ]);

        $response = $this->post('/matriculas-periodo', [
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
        ]);

        $response->assertSessionHasErrors(['plan_pago_id']);
    });

    it('allows student to enroll in a non-special plan', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::factory()->create();
        $matriculaCarrera = MatriculaCarrera::factory()->create([
            'usuario_id' => $user->id,
            'oferta_academica_id' => $oferta->id,
        ]);

        $periodo = PeriodoAcademico::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'fecha_inicio_inscripcion' => now()->subDay()->toDateString(),
            'fecha_fin_inscripcion' => now()->addDay()->toDateString(),
            'estado' => 'inscripcion',
        ]);
        $plan = PlanPago::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'tipo' => 'por_periodo',
        ]);

        $response = $this->post('/matriculas-periodo', [
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('matriculas_periodo', [
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
        ]);
    });
});
