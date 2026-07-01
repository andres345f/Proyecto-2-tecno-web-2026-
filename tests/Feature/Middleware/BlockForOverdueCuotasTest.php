<?php

use App\Models\User;
use App\Models\Cuota;
use App\Models\MatriculaPeriodo;
use App\Models\MatriculaCarrera;
use App\Models\PeriodoAcademico;
use App\Models\OfertaAcademica;
use App\Models\PlanPago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

describe('BlockForOverdueCuotas Middleware', function () {
    it('allows access to allowed routes for student with overdue cuota', function () {
        $student = User::factory()->create([
            'is_estudiante' => true,
            'is_activo' => true,
        ]);

        $oferta = OfertaAcademica::create([
            'nombre' => 'Jovenes',
            'codigo' => 'JOV',
            'descripcion' => 'Malla Jovenes',
        ]);

        $periodo = PeriodoAcademico::create([
            'oferta_academica_id' => $oferta->id,
            'nombre' => '2026-I',
            'tipo' => 'semestral',
            'fecha_inicio' => now()->subMonth(),
            'fecha_fin' => now()->addMonths(5),
            'fecha_inicio_inscripcion' => now()->subMonth(),
            'fecha_fin_inscripcion' => now()->addDays(5),
            'estado' => 'inscripcion',
        ]);

        $plan = PlanPago::factory()->create([
            'oferta_academica_id' => $oferta->id
        ]);

        $matriculaCarrera = MatriculaCarrera::create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        $matriculaPeriodo = MatriculaPeriodo::create([
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        Cuota::create([
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'descripcion' => 'Cuota 1',
            'monto' => 100.00,
            'fecha_vencimiento' => now()->subDays(5),
            'estado' => 'vencido',
        ]);

        Route::middleware('web')->group(function () {
            Route::get('/pagos', function () { return 'PAGOS PAGE'; })->name('pagos.index');
            Route::post('/api/pagos/generar-qr', function () { return 'QR GENERATED'; })->name('api.pagos.generar-qr');
        });

        $response = $this->actingAs($student)->get(route('pagos.index'));
        $response->assertOk();
        $response->assertSee('PAGOS PAGE');

        $response = $this->actingAs($student)->post(route('api.pagos.generar-qr'));
        $response->assertOk();
        $response->assertSee('QR GENERATED');
    });

    it('blocks access to other routes for student with overdue cuota', function () {
        $student = User::factory()->create([
            'is_estudiante' => true,
            'is_activo' => true,
        ]);

        $oferta = OfertaAcademica::create([
            'nombre' => 'Jovenes',
            'codigo' => 'JOV',
            'descripcion' => 'Malla Jovenes',
        ]);

        $periodo = PeriodoAcademico::create([
            'oferta_academica_id' => $oferta->id,
            'nombre' => '2026-I',
            'tipo' => 'semestral',
            'fecha_inicio' => now()->subMonth(),
            'fecha_fin' => now()->addMonths(5),
            'fecha_inicio_inscripcion' => now()->subMonth(),
            'fecha_fin_inscripcion' => now()->addDays(5),
            'estado' => 'inscripcion',
        ]);

        $plan = PlanPago::factory()->create([
            'oferta_academica_id' => $oferta->id
        ]);

        $matriculaCarrera = MatriculaCarrera::create([
            'usuario_id' => $student->id,
            'oferta_academica_id' => $oferta->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        $matriculaPeriodo = MatriculaPeriodo::create([
            'matricula_carrera_id' => $matriculaCarrera->id,
            'periodo_academico_id' => $periodo->id,
            'plan_pago_id' => $plan->id,
            'fecha_matricula' => now(),
            'estado' => 'activo',
        ]);

        Cuota::create([
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'descripcion' => 'Cuota 1',
            'monto' => 100.00,
            'fecha_vencimiento' => now()->subDays(5),
            'estado' => 'vencido',
        ]);

        Route::middleware('web')->group(function () {
            Route::get('/pagos', function () { return 'PAGOS PAGE'; })->name('pagos.index');
            Route::get('/dashboard', function () { return 'DASHBOARD PAGE'; })->name('dashboard');
        });

        $response = $this->actingAs($student)->get(route('dashboard'));
        $response->assertRedirect(route('pagos.index'));
        $response->assertSessionHasErrors(['payment_required']);
    });
});
