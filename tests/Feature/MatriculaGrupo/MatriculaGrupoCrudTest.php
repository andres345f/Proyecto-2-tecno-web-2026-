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
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createStudentWithMatricula(): array
{
    $student = User::factory()->create(['is_estudiante' => true]);
    $oferta = OfertaAcademica::factory()->create();
    $materia = Materia::factory()->create();
    $aula = Aula::factory()->create(['capacidad' => 30]);
    $periodo = PeriodoAcademico::factory()->create([
        'oferta_academica_id' => $oferta->id,
        'fecha_inicio_inscripcion' => now()->subDay()->toDateString(),
        'fecha_fin_inscripcion' => now()->addDay()->toDateString(),
        'estado' => 'inscripcion',
    ]);
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

    return compact('student', 'matriculaPeriodo', 'grupoPeriodo', 'grupo', 'oferta', 'materia', 'aula', 'periodo', 'plan', 'docente');
}

describe('MatriculaGrupo CRUD', function () {

    it('allows student to enroll in a group', function () {
        $data = createStudentWithMatricula();

        $response = $this->actingAs($data['student'])
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $data['matriculaPeriodo']->id,
                'grupo_ids' => [$data['grupoPeriodo']->id],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('matriculas_grupo', [
            'matricula_periodo_id' => $data['matriculaPeriodo']->id,
            'grupo_periodo_id' => $data['grupoPeriodo']->id,
            'estado' => 'inscrito',
        ]);
    });

    it('rejects enrollment when materia not in malla curricular', function () {
        $student = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();
        $periodo = PeriodoAcademico::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'fecha_inicio_inscripcion' => now()->subDay()->toDateString(),
            'fecha_fin_inscripcion' => now()->addDay()->toDateString(),
            'estado' => 'inscripcion',
        ]);
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

        $response = $this->actingAs($student)
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $matriculaPeriodo->id,
                'grupo_ids' => [$grupoPeriodo->id],
            ]);

        $response->assertSessionHasErrors('grupo_id');
    });

    it('rejects enrollment when prerequisite not met', function () {
        $student = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $materiaBase = Materia::factory()->create();
        $materia = Materia::factory()->create();

        $aula = Aula::factory()->create(['capacidad' => 30]);
        $periodo = PeriodoAcademico::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'fecha_inicio_inscripcion' => now()->subDay()->toDateString(),
            'fecha_fin_inscripcion' => now()->addDay()->toDateString(),
            'estado' => 'inscripcion',
        ]);
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

        $response = $this->actingAs($student)
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $matriculaPeriodo->id,
                'grupo_ids' => [$grupoPeriodo->id],
            ]);

        $response->assertSessionHasErrors('grupo_id');
    });

    it('rejects enrollment when prerequisite nota_final below 51', function () {
        $student = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $materiaBase = Materia::factory()->create();
        $materia = Materia::factory()->create();

        $aula = Aula::factory()->create(['capacidad' => 30]);
        $periodo = PeriodoAcademico::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'fecha_inicio_inscripcion' => now()->subDay()->toDateString(),
            'fecha_fin_inscripcion' => now()->addDay()->toDateString(),
            'estado' => 'inscripcion',
        ]);
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

        $response = $this->actingAs($student)
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $matriculaPeriodo->id,
                'grupo_ids' => [$grupoPeriodo->id],
            ]);

        $response->assertSessionHasErrors('grupo_id');
    });

    it('rejects enrollment when group capacity exceeded', function () {
        $data = createStudentWithMatricula();

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
                'periodo_academico_id' => $data['matriculaPeriodo']->periodo_academico_id,
                'plan_pago_id' => PlanPago::factory()->create(['oferta_academica_id' => $data['oferta']->id])->id,
                'fecha_matricula' => now(),
                'estado' => 'activo',
            ]);
            MatriculaGrupo::create([
                'matricula_periodo_id' => $otherMatriculaPeriodo->id,
                'grupo_periodo_id' => $data['grupoPeriodo']->id,
                'estado' => 'en_curso',
            ]);
        }

        $response = $this->actingAs($data['student'])
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $data['matriculaPeriodo']->id,
                'grupo_ids' => [$data['grupoPeriodo']->id],
            ]);

        $response->assertSessionHasErrors('grupo_id');
    });

    it('rejects duplicate enrollment in same group', function () {
        $data = createStudentWithMatricula();

        $this->actingAs($data['student'])
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $data['matriculaPeriodo']->id,
                'grupo_ids' => [$data['grupoPeriodo']->id],
            ]);

        $response = $this->actingAs($data['student'])
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $data['matriculaPeriodo']->id,
                'grupo_ids' => [$data['grupoPeriodo']->id],
            ]);

        $response->assertSessionHasErrors('grupo_ids');
    });

    it('allows student to withdraw from group', function () {
        $data = createStudentWithMatricula();

        $this->actingAs($data['student'])
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $data['matriculaPeriodo']->id,
                'grupo_ids' => [$data['grupoPeriodo']->id],
            ]);

        $matriculaGrupo = MatriculaGrupo::where('matricula_periodo_id', $data['matriculaPeriodo']->id)
            ->where('grupo_periodo_id', $data['grupoPeriodo']->id)
            ->first();

        $response = $this->actingAs($data['student'])
            ->delete(route('matriculas-grupo.destroy', $matriculaGrupo));

        $response->assertRedirect();
        $this->assertDatabaseHas('matriculas_grupo', [
            'id' => $matriculaGrupo->id,
            'estado' => 'retirado',
        ]);
    });

    it('shows matricula grupo detail', function () {
        $data = createStudentWithMatricula();

        $this->actingAs($data['student'])
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $data['matriculaPeriodo']->id,
                'grupo_ids' => [$data['grupoPeriodo']->id],
            ]);

        $matriculaGrupo = MatriculaGrupo::where('matricula_periodo_id', $data['matriculaPeriodo']->id)
            ->where('grupo_periodo_id', $data['grupoPeriodo']->id)
            ->first();

        $response = $this->actingAs($data['student'])
            ->get(route('matriculas-grupo.show', $matriculaGrupo));

        $response->assertStatus(200);
    });

    it('lists enrolled groups for student', function () {
        $data = createStudentWithMatricula();

        $this->actingAs($data['student'])
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $data['matriculaPeriodo']->id,
                'grupo_ids' => [$data['grupoPeriodo']->id],
            ]);

        $response = $this->actingAs($data['student'])
            ->get(route('matriculas-grupo.index'));

        $response->assertStatus(200);
    });

    it('rejects unauthenticated access', function () {
        $response = $this->get(route('matriculas-grupo.index'));
        $response->assertRedirect('/login');
    });

    it('validates required fields', function () {
        $student = User::factory()->create(['is_estudiante' => true]);

        $response = $this->actingAs($student)
            ->post(route('matriculas-grupo.store'), []);

        $response->assertSessionHasErrors(['matricula_periodo_id', 'grupo_ids']);
    });

    it('validates matricula_periodo_id exists', function () {
        $student = User::factory()->create(['is_estudiante' => true]);

        $response = $this->actingAs($student)
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => 9999,
                'grupo_ids' => [1],
            ]);

        $response->assertSessionHasErrors('matricula_periodo_id');
    });

    it('validates grupo_id exists', function () {
        $student = User::factory()->create(['is_estudiante' => true]);

        $response = $this->actingAs($student)
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => 1,
                'grupo_ids' => [9999],
            ]);

        $response->assertSessionHasErrors('grupo_ids.0');
    });

    it('denies group enrollment when today is not within registration dates', function () {
        $student = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();
        $aula = Aula::factory()->create(['capacidad' => 30]);
        // Period with enrollment dates in the past
        $periodo = PeriodoAcademico::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'fecha_inicio_inscripcion' => now()->subDays(10)->toDateString(),
            'fecha_fin_inscripcion' => now()->subDays(5)->toDateString(),
            'estado' => 'inscripcion',
        ]);
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

        $response = $this->actingAs($student)
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $matriculaPeriodo->id,
                'grupo_ids' => [$grupoPeriodo->id],
            ]);

        $response->assertStatus(403);
    });

    it('allows admin to download grades template', function () {
        $admin = User::factory()->create(['is_estudiante' => false, 'is_secretaria' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();
        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
        $grupo = Grupo::factory()->create(['materia_id' => $materia->id]);

        $grupoPeriodo = GrupoPeriodo::create([
            'grupo_id' => $grupo->id,
            'periodo_academico_id' => $periodo->id,
            'cupo_maximo' => 30,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('matriculas-grupo.plantilla-notas', $grupoPeriodo->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="plantilla_notas_grupo_' . $grupo->codigo . '.csv"');
    });

    it('allows admin to import grades via csv', function () {
        $admin = User::factory()->create(['is_estudiante' => false, 'is_secretaria' => true]);
        $student = User::factory()->create(['is_estudiante' => true, 'codigo_estudiante' => '123456789']);
        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();
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
        $grupo = Grupo::factory()->create(['materia_id' => $materia->id]);

        $grupoPeriodo = GrupoPeriodo::create([
            'grupo_id' => $grupo->id,
            'periodo_academico_id' => $periodo->id,
            'cupo_maximo' => 30,
        ]);

        $matriculaGrupo = MatriculaGrupo::create([
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'grupo_periodo_id' => $grupoPeriodo->id,
            'estado' => 'inscrito',
        ]);

        // Create a temporary CSV file
        $csvContent = "codigo_estudiante,nota_final\n";
        $csvContent .= "123456789,85\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempFile,
            'notas.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->actingAs($admin)
            ->post(route('matriculas-grupo.importar-notas', $grupoPeriodo->id), [
                'archivo' => $uploadedFile,
            ]);

        $response->assertStatus(302); // Redirect back
        $response->assertSessionHas('success');

        $matriculaGrupo->refresh();
        expect(floatval($matriculaGrupo->nota_final))->toEqual(85.0);
        expect($matriculaGrupo->estado)->toBe('aprobado');

        @unlink($tempFile);
    });

    it('rejects enrollment when group schedules overlap', function () {
        $student = User::factory()->create(['is_estudiante' => true]);
        $oferta = OfertaAcademica::factory()->create();
        $materia1 = Materia::factory()->create();
        $materia2 = Materia::factory()->create();

        $aula = Aula::factory()->create(['capacidad' => 30]);
        $periodo = PeriodoAcademico::factory()->create([
            'oferta_academica_id' => $oferta->id,
            'fecha_inicio_inscripcion' => now()->subDay()->toDateString(),
            'fecha_fin_inscripcion' => now()->addDay()->toDateString(),
            'estado' => 'inscripcion',
        ]);
        $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

        MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia1->id,
            'semestre_orden' => 1,
        ]);
        MallaCurricular::create([
            'oferta_academica_id' => $oferta->id,
            'materia_id' => $materia2->id,
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

        $grupo1 = Grupo::factory()->create(['materia_id' => $materia1->id]);
        $grupo2 = Grupo::factory()->create(['materia_id' => $materia2->id]);

        $grupoPeriodo1 = GrupoPeriodo::create([
            'grupo_id' => $grupo1->id,
            'periodo_academico_id' => $periodo->id,
            'cupo_maximo' => 30,
        ]);

        $grupoPeriodo2 = GrupoPeriodo::create([
            'grupo_id' => $grupo2->id,
            'periodo_academico_id' => $periodo->id,
            'cupo_maximo' => 30,
        ]);

        // Create overlapping schedules
        // Grupo 1: Lunes 08:00:00 - 09:30:00
        Horario::factory()->create([
            'grupo_periodo_id' => $grupoPeriodo1->id,
            'dia' => 'Lunes',
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:30:00',
            'aula_id' => $aula->id,
        ]);

        // Grupo 2: Lunes 09:00:00 - 10:30:00 (overlaps with Grupo 1)
        Horario::factory()->create([
            'grupo_periodo_id' => $grupoPeriodo2->id,
            'dia' => 'Lunes',
            'hora_inicio' => '09:00:00',
            'hora_fin' => '10:30:00',
            'aula_id' => $aula->id,
        ]);

        // Scenario A: Try to enroll in both simultaneously
        $response = $this->actingAs($student)
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $matriculaPeriodo->id,
                'grupo_ids' => [$grupoPeriodo1->id, $grupoPeriodo2->id],
            ]);

        $response->assertSessionHasErrors('grupo_ids');

        // Scenario B: Enroll in group 1 first, then try to enroll in group 2
        MatriculaGrupo::create([
            'matricula_periodo_id' => $matriculaPeriodo->id,
            'grupo_periodo_id' => $grupoPeriodo1->id,
            'estado' => 'inscrito',
        ]);

        $response2 = $this->actingAs($student)
            ->post(route('matriculas-grupo.store'), [
                'matricula_periodo_id' => $matriculaPeriodo->id,
                'grupo_ids' => [$grupoPeriodo2->id],
            ]);

        $response2->assertSessionHasErrors('grupo_ids');
    });
});
