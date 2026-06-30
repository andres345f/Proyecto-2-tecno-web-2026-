<?php

use App\Models\Cuota;
use App\Models\Materia;
use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\OfertaAcademica;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use App\Models\User;
use App\Services\BuscadorGlobalService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new BuscadorGlobalService;
});

describe('BuscadorGlobalService', function () {

    it('searches students by name', function () {
        User::factory()->create(['name' => 'Carlos Gomez', 'is_estudiante' => true]);
        User::factory()->create(['name' => 'Ana Perez', 'is_estudiante' => true]);

        $results = $this->service->search('Carlos');

        expect($results)->toHaveKey('usuarios');
        expect($results['usuarios'])->toHaveCount(1);
        expect($results['usuarios'][0]['nombre'])->toBe('Carlos Gomez');
    });

    it('searches students by email', function () {
        User::factory()->create(['email' => 'carlos@test.com', 'is_estudiante' => true]);

        $results = $this->service->search('carlos@test');

        expect($results['usuarios'])->toHaveCount(1);
    });

    it('searches materias by nombre', function () {
        Materia::factory()->create(['nombre' => 'Fisica Aplicada']);
        Materia::factory()->create(['nombre' => 'Matematicas Basicas']);

        $results = $this->service->search('Fisica');

        expect($results)->toHaveKey('materias');
        expect($results['materias'])->toHaveCount(1);
        expect($results['materias'][0]['nombre'])->toBe('Fisica Aplicada');
    });

    it('searches materias by codigo', function () {
        Materia::factory()->create(['codigo' => 'RED-FIS', 'nombre' => 'Fisica']);

        $results = $this->service->search('RED-FIS');

        expect($results['materias'])->toHaveCount(1);
    });

    it('searches cuotas by descripcion', function () {
        $user = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

        $carrera = MatriculaCarrera::create([
            'usuario_id' => $user->id,
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

        Cuota::create([
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'descripcion' => 'Matricula - Semestre 2026-I',
            'monto' => 50.00,
            'fecha_vencimiento' => now()->addMonth(),
            'estado' => 'pendiente',
        ]);

        $results = $this->service->search('Matricula');

        expect($results)->toHaveKey('cuotas');
        expect($results['cuotas'])->toHaveCount(1);
    });

    it('returns empty results when no match found', function () {
        $results = $this->service->search('ZZZZNONEXISTENT');

        expect($results['usuarios'])->toHaveCount(0);
        expect($results['materias'])->toHaveCount(0);
        expect($results['cuotas'])->toHaveCount(0);
    });

    it('returns mixed results across all types', function () {
        User::factory()->create(['name' => 'Test Student', 'is_estudiante' => true]);
        Materia::factory()->create(['nombre' => 'Test Materia', 'codigo' => 'TEST-01']);

        $results = $this->service->search('Test');

        expect($results['usuarios'])->toHaveCount(1);
        expect($results['materias'])->toHaveCount(1);
    });

    it('does not return non-student users in search', function () {
        User::factory()->create(['name' => 'Test Director', 'is_director' => true, 'is_estudiante' => false]);
        User::factory()->create(['name' => 'Test Student', 'is_estudiante' => true]);

        $results = $this->service->search('Test');

        // Should only return the student
        expect($results['usuarios'])->toHaveCount(1);
        expect($results['usuarios'][0]['nombre'])->toBe('Test Student');
    });
});
