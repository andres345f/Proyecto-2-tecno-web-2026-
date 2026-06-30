<?php

use App\Models\Aula;
use App\Models\Grupo;
use App\Models\GrupoPeriodo;
use App\Models\Horario;
use App\Models\MallaCurricular;
use App\Models\Materia;
use App\Models\MatriculaCarrera;
use App\Models\MatriculaGrupo;
use App\Models\MatriculaPeriodo;
use App\Models\OfertaAcademica;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use App\Models\User;
use App\Services\ValidadorPrerrequisitos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function createScenario(): array
{
    $student = User::factory()->create(['is_estudiante' => true]);
    $oferta = OfertaAcademica::factory()->create();
    $materia = Materia::factory()->create();
    $aula = Aula::factory()->create(['capacidad' => 30]);
    $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
    $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

    MallaCurricular::create([
        'oferta_academica_id' => $oferta->id,
        'materia_id' => $materia->id,
        'semestre_orden' => 1,
    ]);

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

    $docente = User::factory()->create(['is_profesor' => true]);
    $grupo = Grupo::factory()->create([
        'materia_id' => $materia->id,
    ]);

    $grupoPeriodo = GrupoPeriodo::create([
        'grupo_id' => $grupo->id,
        'periodo_academico_id' => $periodo->id,
        'docente_id' => $docente->id,
        'cupo_maximo' => 30,
    ]);

    Horario::factory()->create([
        'grupo_periodo_id' => $grupoPeriodo->id,
        'aula_id' => $aula->id,
    ]);

    return compact('student', 'oferta', 'materia', 'aula', 'periodo', 'plan', 'carrera', 'matriculaPeriodo', 'grupoPeriodo', 'grupo');
}

describe('ValidadorPrerrequisitos', function () {

    it('returns true when all checks pass', function () {
        $data = createScenario();
        $validator = new ValidadorPrerrequisitos;

        $result = $validator->validar($data['matriculaPeriodo'], $data['grupoPeriodo']);

        expect($result)->toBeTrue();
    });

    it('rejects when materia not in malla curricular', function () {
        $student = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();
        $aula = Aula::factory()->create(['capacidad' => 30]);
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

        $docente = User::factory()->create(['is_profesor' => true]);
        $grupo = Grupo::factory()->create([
            'materia_id' => $materia->id,
        ]);

        $grupoPeriodo = GrupoPeriodo::create([
            'grupo_id' => $grupo->id,
            'periodo_academico_id' => $periodo->id,
            'docente_id' => $docente->id,
            'cupo_maximo' => 30,
        ]);

        $validator = new ValidadorPrerrequisitos;

        try {
            $validator->validar($matriculaPeriodo, $grupoPeriodo);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            expect($e->getMessage())->toContain('malla curricular');
        }
    });

    it('rejects when prerequisite not enrolled', function () {
        $student = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $materiaBase = Materia::factory()->create();
        $materia = Materia::factory()->create();
        $aula = Aula::factory()->create(['capacidad' => 30]);
        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

        $mallaBase = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materiaBase->id,
            'semestre_orden' => 1,
        ]);
        $mallaMateria = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 2,
        ]);

        $mallaMateria->prerrequisitos()->attach($mallaBase->id);

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

        $docente = User::factory()->create(['is_profesor' => true]);
        $grupo = Grupo::factory()->create([
            'materia_id' => $materia->id,
        ]);

        $grupoPeriodo = GrupoPeriodo::create([
            'grupo_id' => $grupo->id,
            'periodo_academico_id' => $periodo->id,
            'docente_id' => $docente->id,
            'cupo_maximo' => 30,
        ]);

        Horario::factory()->create([
            'grupo_periodo_id' => $grupoPeriodo->id,
            'aula_id' => $aula->id,
        ]);

        $validator = new ValidadorPrerrequisitos;

        try {
            $validator->validar($matriculaPeriodo, $grupoPeriodo);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            expect($e->getMessage())->toContain('prerrequisito');
        }
    });

    it('rejects when prerequisite nota_final below 51', function () {
        $student = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $materiaBase = Materia::factory()->create();
        $materia = Materia::factory()->create();
        $aula = Aula::factory()->create(['capacidad' => 30]);
        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

        $mallaBase = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materiaBase->id,
            'semestre_orden' => 1,
        ]);
        $mallaMateria = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 2,
        ]);

        $mallaMateria->prerrequisitos()->attach($mallaBase->id);

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

        $docente = User::factory()->create(['is_profesor' => true]);
        $grupoBase = Grupo::factory()->create([
            'materia_id' => $materiaBase->id,
        ]);

        $grupoPeriodoBase = GrupoPeriodo::create([
            'grupo_id' => $grupoBase->id,
            'periodo_academico_id' => $periodo->id,
            'docente_id' => $docente->id,
            'cupo_maximo' => 30,
        ]);

        MatriculaGrupo::create([
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'grupo_periodo_id' => $grupoPeriodoBase->id,
            'nota_final' => 45,
            'estado' => 'reprobado',
        ]);

        $grupo = Grupo::factory()->create([
            'materia_id' => $materia->id,
        ]);

        $grupoPeriodo = GrupoPeriodo::create([
            'grupo_id' => $grupo->id,
            'periodo_academico_id' => $periodo->id,
            'docente_id' => $docente->id,
            'cupo_maximo' => 30,
        ]);

        Horario::factory()->create([
            'grupo_periodo_id' => $grupoPeriodo->id,
            'aula_id' => $aula->id,
        ]);

        $validator = new ValidadorPrerrequisitos;

        try {
            $validator->validar($matriculaPeriodo, $grupoPeriodo);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            expect($e->getMessage())->toContain('Prerrequisito no aprobado');
        }
    });

    it('rejects when group capacity exceeded', function () {
        $data = createScenario();
        $data['grupoPeriodo']->update(['cupo_maximo' => 30]);

        for ($i = 0; $i < 30; $i++) {
            $otherStudent = User::factory()->create(['is_estudiante' => true]);
            $otherCarrera = MatriculaCarrera::create([
                'usuario_id' => $otherStudent->id,
                'oferta_academica_id' => $data['oferta']->id,
                'fecha_matricula' => now(),
                'estado' => 'activo',
            ]);
            $otherMatriculaPeriodo = MatriculaPeriodo::create([
                'matricula_carrera_id' => $otherCarrera->id,
                'periodo_academico_id' => $data['periodo']->id,
                'plan_pago_id' => $data['plan']->id,
                'fecha_matricula' => now(),
                'estado' => 'activo',
            ]);
            MatriculaGrupo::create([
                'matricula_periodo_id' => $otherMatriculaPeriodo->id,
                'grupo_periodo_id' => $data['grupoPeriodo']->id,
                'estado' => 'en_curso',
            ]);
        }

        $validator = new ValidadorPrerrequisitos;

        try {
            $validator->validar($data['matriculaPeriodo'], $data['grupoPeriodo']);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            expect($e->getMessage())->toContain('capacidad');
        }
    });

    it('rejects when already enrolled in same group', function () {
        $data = createScenario();

        MatriculaGrupo::create([
            'matricula_periodo_id' => $data['matriculaPeriodo']->id,
            'grupo_periodo_id' => $data['grupoPeriodo']->id,
            'estado' => 'en_curso',
        ]);

        $validator = new ValidadorPrerrequisitos;

        try {
            $validator->validar($data['matriculaPeriodo'], $data['grupoPeriodo']);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            expect($e->getMessage())->toContain('inscrito');
        }
    });

    it('passes when prerequisite is approved with nota_final >= 51', function () {
        $student = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $materiaBase = Materia::factory()->create();
        $materia = Materia::factory()->create();
        $aula = Aula::factory()->create(['capacidad' => 30]);
        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

        $mallaBase = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materiaBase->id,
            'semestre_orden' => 1,
        ]);
        $mallaMateria = MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia->id,
            'semestre_orden' => 2,
        ]);

        $mallaMateria->prerrequisitos()->attach($mallaBase->id);

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

        $docente = User::factory()->create(['is_profesor' => true]);
        $grupoBase = Grupo::factory()->create([
            'materia_id' => $materiaBase->id,
        ]);

        $grupoPeriodoBase = GrupoPeriodo::create([
            'grupo_id' => $grupoBase->id,
            'periodo_academico_id' => $periodo->id,
            'docente_id' => $docente->id,
            'cupo_maximo' => 30,
        ]);

        MatriculaGrupo::create([
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'grupo_periodo_id' => $grupoPeriodoBase->id,
            'nota_final' => 75,
            'estado' => 'aprobado',
        ]);

        $grupo = Grupo::factory()->create([
            'materia_id' => $materia->id,
        ]);

        $grupoPeriodo = GrupoPeriodo::create([
            'grupo_id' => $grupo->id,
            'periodo_academico_id' => $periodo->id,
            'docente_id' => $docente->id,
            'cupo_maximo' => 30,
        ]);

        Horario::factory()->create([
            'grupo_periodo_id' => $grupoPeriodo->id,
            'aula_id' => $aula->id,
        ]);

        $validator = new ValidadorPrerrequisitos;
        $result = $validator->validar($matriculaPeriodo, $grupoPeriodo);

        expect($result)->toBeTrue();
    });
});
