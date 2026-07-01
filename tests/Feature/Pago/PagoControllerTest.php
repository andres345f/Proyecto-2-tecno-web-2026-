<?php

use App\Models\Cuota;
use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\OfertaAcademica;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createStudentWithCuota(): array
{
    $estudiante = User::factory()->create(['is_estudiante' => true]);
    $oferta = OfertaAcademica::factory()->create();
    $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
    $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

    $carrera = MatriculaCarrera::create([
        'usuario_id' => $estudiante->id,
        'oferta_academica_id' => $oferta->id,
        'fecha_matricula' => now(),
        'estado' => 'activo',
    ]);

    $matriculaPeriodo = MatriculaPeriodo::create([
        'matricula_carrera_id' => $carrera->id,
        'periodo_academico_id' => $periodo->id,
        'plan_pago_id' => $plan->id,
        'fecha_matricula' => now(),
        'estado' => 'activo',
    ]);

    $cuota = Cuota::create([
        'matricula_periodo_id' => $matriculaPeriodo->id,
        'descripcion' => 'Cuota Test',
        'monto' => 50.00,
        'fecha_vencimiento' => now()->addMonth(),
        'estado' => 'pendiente',
    ]);

    return compact('estudiante', 'cuota', 'oferta', 'periodo');
}

describe('PagoController — QR Generation', function () {

    it('allows student to generate QR for own pending cuota', function () {
        $data = createStudentWithCuota();

        $response = $this->actingAs($data['estudiante'])
            ->postJson(route('api.pagos.generar-qr'), [
                'cuota_id' => $data['cuota']->id,
            ]);

        $response->assertOk()
            ->assertJsonStructure(['qr_image', 'transaccion_id', 'status'])
            ->assertJson(['status' => 'pending']);
    });

    it('rejects unauthenticated access to generar-qr', function () {
        $cuota = Cuota::factory()->create();

        $response = $this->postJson(route('api.pagos.generar-qr'), [
            'cuota_id' => $cuota->id,
        ]);

        $response->assertUnauthorized();
    });

    it('rejects student generating QR for another student cuota', function () {
        $student1 = User::factory()->create(['is_estudiante' => true]);
        $student2 = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

        $carrera1 = MatriculaCarrera::create([
            'usuario_id' => $student1->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        $matriculaPeriodo1 = MatriculaPeriodo::create([
            'matricula_carrera_id' => $carrera1->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        $cuota = Cuota::create([
            'matricula_periodo_id' => $matriculaPeriodo1->id,
            'descripcion' => 'Cuota Student1',
            'monto' => 50.00,
            'fecha_vencimiento' => now()->addMonth(),
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($student2)
            ->postJson(route('api.pagos.generar-qr'), [
                'cuota_id' => $cuota->id,
            ]);

        $response->assertForbidden();
    });

    it('rejects QR generation for already paid cuota', function () {
        $data = createStudentWithCuota();
        $data['cuota']->update(['estado' => 'pagado']);

        $response = $this->actingAs($data['estudiante'])
            ->postJson(route('api.pagos.generar-qr'), [
                'cuota_id' => $data['cuota']->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('cuota_id');
    });

    it('validates cuota_id is required and exists', function () {
        $estudiante = User::factory()->create(['is_estudiante' => true]);

        $response = $this->actingAs($estudiante)
            ->postJson(route('api.pagos.generar-qr'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('cuota_id');
    });

    it('validates cuota_id exists in database', function () {
        $estudiante = User::factory()->create(['is_estudiante' => true]);

        $response = $this->actingAs($estudiante)
            ->postJson(route('api.pagos.generar-qr'), [
                'cuota_id' => 99999,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('cuota_id');
    });
});

describe('PagoController — Status Polling', function () {

    it('returns payment status for valid transaction', function () {
        $data = createStudentWithCuota();
        $this->actingAs($data['estudiante']);
        $qrResponse = $this->postJson(route('api.pagos.generar-qr'), [
            'cuota_id' => $data['cuota']->id,
        ])->json();

        $response = $this->getJson(route('api.pagos.estado', $qrResponse['transaccion_id']));

        $response->assertOk()
            ->assertJson(['status' => 'pendiente']);
    });

    it('returns not_found for unknown transaction', function () {
        $estudiante = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($estudiante);

        $response = $this->getJson(route('api.pagos.estado', 'PF-NONEXISTENT'));

        $response->assertOk()
            ->assertJson(['status' => 'not_found']);
    });
});

describe('PagoController — Webhook', function () {

    it('processes webhook and updates pago + cuota status', function () {
        $data = createStudentWithCuota();
        $this->actingAs($data['estudiante']);
        $qrResponse = $this->postJson(route('api.pagos.generar-qr'), [
            'cuota_id' => $data['cuota']->id,
        ])->json();

        $response = $this->postJson(route('api.pagos.webhook'), [
            'transaccion_id' => $qrResponse['transaccion_id'],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('pagos', [
            'transaccion_id' => $qrResponse['transaccion_id'],
            'estado' => 'completado',
        ]);

        $data['cuota']->refresh();
        expect($data['cuota']->estado)->toBe('pagado');
    });

    it('returns error for unknown transaction in webhook', function () {
        $estudiante = User::factory()->create(['is_estudiante' => true]);
        $this->actingAs($estudiante);

        $response = $this->postJson(route('api.pagos.webhook'), [
            'transaccion_id' => 'PF-NONEXISTENT',
        ]);

        $response->assertStatus(404);
    });
});

describe('PagoController — Index Page', function () {

    it('allows student to view their cuotas', function () {
        $data = createStudentWithCuota();

        $response = $this->actingAs($data['estudiante'])
            ->get(route('pagos.index'));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Pago/Index'));
    });

    it('allows owner to view all payments on the admin payments dashboard', function () {
        $owner = User::factory()->create(['is_propietario' => true]);

        $response = $this->actingAs($owner)
            ->get(route('pagos.index'));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Pago/AdminIndex'));
    });

    it('allows director to view all payments on the admin payments dashboard', function () {
        $director = User::factory()->create(['is_director' => true]);

        $response = $this->actingAs($director)
            ->get(route('pagos.index'));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Pago/AdminIndex'));
    });

    it('allows secretary to view all payments on the admin payments dashboard', function () {
        $secretary = User::factory()->create(['is_secretaria' => true]);

        $response = $this->actingAs($secretary)
            ->get(route('pagos.index'));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Pago/AdminIndex'));
    });

    it('automatically updates expired cuotas to vencido when visiting index', function () {
        $student = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

        $carrera = MatriculaCarrera::create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        $matriculaPeriodo = MatriculaPeriodo::create([
            'matricula_carrera_id' => $carrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        $cuota = Cuota::create([
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'descripcion' => 'Cuota Vencida',
            'monto' => 50.00,
            'fecha_vencimiento' => now()->subDays(1),
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($student)
            ->get(route('pagos.index'));

        $response->assertStatus(200);

        $cuota->refresh();
        expect($cuota->estado)->toBe('vencido');
    });
});
