<?php

use App\Models\Cuota;
use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\OfertaAcademica;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use App\Models\User;
use App\Services\PagoFacilService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createPendingCuotaForStudent(): Cuota
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

    return Cuota::create([
        'matricula_periodo_id' => $matriculaPeriodo->id,
        'descripcion' => 'Cuota Test',
        'monto' => 50.00,
        'fecha_vencimiento' => now()->addMonth(),
        'estado' => 'pendiente',
    ]);
}

describe('PagoFacilService', function () {

    it('generates QR and creates pago record with pending status', function () {
        $cuota = createPendingCuotaForStudent();
        $service = new PagoFacilService;

        $result = $service->generarQr($cuota);

        expect($result)->toHaveKey('qr_image');
        expect($result)->toHaveKey('transaccion_id');
        expect($result)->toHaveKey('status');
        expect($result['status'])->toBe('pending');
        expect($result['transaccion_id'])->toStartWith('PF-');

        $this->assertDatabaseHas('pagos', [
            'cuota_id' => $cuota->id,
            'estado' => 'pendiente',
            'transaccion_id' => $result['transaccion_id'],
        ]);
    });

    it('returns not_found for unknown transaction', function () {
        $service = new PagoFacilService;
        $result = $service->consultarEstado('PF-NONEXISTENT');

        expect($result['status'])->toBe('not_found');
    });

    it('returns pending status for existing transaction', function () {
        $cuota = createPendingCuotaForStudent();
        $service = new PagoFacilService;
        $qr = $service->generarQr($cuota);

        $result = $service->consultarEstado($qr['transaccion_id']);

        expect($result['status'])->toBe('pendiente');
        expect($result)->toHaveKey('monto');
    });

    it('simulates payment and updates cuota status', function () {
        $cuota = createPendingCuotaForStudent();
        $service = new PagoFacilService;
        $qr = $service->generarQr($cuota);

        $result = $service->simularPago($qr['transaccion_id']);

        expect($result)->toBeTrue();

        $this->assertDatabaseHas('pagos', [
            'transaccion_id' => $qr['transaccion_id'],
            'estado' => 'completado',
        ]);

        $cuota->refresh();
        expect($cuota->estado)->toBe('pagado');
    });

    it('returns false when simulating payment for unknown transaction', function () {
        $service = new PagoFacilService;
        $result = $service->simularPago('PF-NONEXISTENT');
        expect($result)->toBeFalse();
    });

    it('cannot generate QR for already paid cuota', function () {
        $cuota = createPendingCuotaForStudent();
        $cuota->update(['estado' => 'pagado']);
        $service = new PagoFacilService;

        $result = $service->generarQr($cuota);

        expect($result)->toHaveKey('error');
        expect($result['error'])->toContain('pagada');
    });

    it('returns correct status after payment simulation', function () {
        $cuota = createPendingCuotaForStudent();
        $service = new PagoFacilService;
        $qr = $service->generarQr($cuota);
        $service->simularPago($qr['transaccion_id']);

        $result = $service->consultarEstado($qr['transaccion_id']);

        expect($result['status'])->toBe('completado');
    });
});
