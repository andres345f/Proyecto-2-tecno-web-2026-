<?php

use App\Models\Entrega;
use App\Models\Grupo;
use App\Models\GrupoPeriodo;
use App\Models\Materia;
use App\Models\MatriculaCarrera;
use App\Models\MatriculaGrupo;
use App\Models\MatriculaPeriodo;
use App\Models\OfertaAcademica;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createTareaForStudent(): array
{
    $profesor = User::factory()->create(['is_profesor' => true]);
    $estudiante = User::factory()->create(['is_estudiante' => true]);
    $oferta = OfertaAcademica::factory()->create();
    $materia = Materia::factory()->create();
    $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);
    $plan = PlanPago::factory()->create(['oferta_academica_id' => $oferta->id]);

    $grupo = Grupo::factory()->create([
        'materia_id' => $materia->id,
    ]);

    $grupoPeriodo = GrupoPeriodo::create([
        'grupo_id' => $grupo->id,
        'periodo_academico_id' => $periodo->id,
        'docente_id' => $profesor->id,
        'cupo_maximo' => 30,
    ]);

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

    MatriculaGrupo::create([
        'matricula_periodo_id' => $matriculaPeriodo->id,
        'grupo_periodo_id' => $grupoPeriodo->id,
        'estado' => 'en_curso',
    ]);

    $tarea = Tarea::factory()->create([
        'grupo_periodo_id' => $grupoPeriodo->id,
        'fecha_vencimiento' => now()->addWeek(),
    ]);

    return compact('profesor', 'estudiante', 'grupoPeriodo', 'tarea', 'oferta', 'materia', 'periodo');
}

describe('Entrega Submission', function () {

    it('allows student to submit file for tarea', function () {
        Storage::fake('local');
        $data = createTareaForStudent();

        $file = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $response = $this->actingAs($data['estudiante'])
            ->post(route('entregas.store'), [
                'tarea_id' => $data['tarea']->id,
                'archivo' => $file,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('entregas', [
            'tarea_id' => $data['tarea']->id,
            'usuario_id' => $data['estudiante']->id,
        ]);
    });

    it('rejects professor from submitting entrega', function () {
        $data = createTareaForStudent();

        $file = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $response = $this->actingAs($data['profesor'])
            ->post(route('entregas.store'), [
                'tarea_id' => $data['tarea']->id,
                'archivo' => $file,
            ]);

        $response->assertForbidden();
    });

    it('rejects submission after deadline', function () {
        $data = createTareaForStudent();
        $data['tarea']->update(['fecha_vencimiento' => now()->subDay()]);

        $file = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $response = $this->actingAs($data['estudiante'])
            ->post(route('entregas.store'), [
                'tarea_id' => $data['tarea']->id,
                'archivo' => $file,
            ]);

        $response->assertSessionHasErrors('tarea_id');
    });

    it('rejects duplicate submission for same tarea', function () {
        Storage::fake('local');
        $data = createTareaForStudent();

        $file1 = UploadedFile::fake()->create('doc1.pdf', 100, 'application/pdf');
        $this->actingAs($data['estudiante'])
            ->post(route('entregas.store'), [
                'tarea_id' => $data['tarea']->id,
                'archivo' => $file1,
            ]);

        $file2 = UploadedFile::fake()->create('doc2.pdf', 100, 'application/pdf');
        $response = $this->actingAs($data['estudiante'])
            ->post(route('entregas.store'), [
                'tarea_id' => $data['tarea']->id,
                'archivo' => $file2,
            ]);

        $response->assertSessionHasErrors('tarea_id');
    });

    it('validates file type is pdf, doc, or docx', function () {
        $data = createTareaForStudent();

        $file = UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($data['estudiante'])
            ->post(route('entregas.store'), [
                'tarea_id' => $data['tarea']->id,
                'archivo' => $file,
            ]);

        $response->assertSessionHasErrors('archivo');
    });

    it('validates file size max 10MB', function () {
        $data = createTareaForStudent();

        $file = UploadedFile::fake()->create('large.pdf', 11000, 'application/pdf');

        $response = $this->actingAs($data['estudiante'])
            ->post(route('entregas.store'), [
                'tarea_id' => $data['tarea']->id,
                'archivo' => $file,
            ]);

        $response->assertSessionHasErrors('archivo');
    });

    it('validates student is enrolled in tarea grupo', function () {
        $profesor = User::factory()->create(['is_profesor' => true]);
        $estudiante = User::factory()->create(['is_estudiante' => true]);
        
        $oferta = OfertaAcademica::factory()->create();
        $materia = Materia::factory()->create();
        $periodo = PeriodoAcademico::factory()->create(['oferta_academica_id' => $oferta->id]);

        $grupo = Grupo::factory()->create([
            'materia_id' => $materia->id,
        ]);

        $grupoPeriodo = GrupoPeriodo::create([
            'grupo_id' => $grupo->id,
            'periodo_academico_id' => $periodo->id,
            'docente_id' => $profesor->id,
            'cupo_maximo' => 30,
        ]);

        $tarea = Tarea::factory()->create(['grupo_periodo_id' => $grupoPeriodo->id, 'fecha_vencimiento' => now()->addWeek()]);

        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $response = $this->actingAs($estudiante)
            ->post(route('entregas.store'), [
                'tarea_id' => $tarea->id,
                'archivo' => $file,
            ]);

        $response->assertSessionHasErrors('tarea_id');
    });

    it('allows professor to grade entrega', function () {
        $data = createTareaForStudent();
        $data['tarea']->update(['puntaje_maximo' => 100]);
        $entrega = Entrega::factory()->create([
            'tarea_id' => $data['tarea']->id,
            'usuario_id' => $data['estudiante']->id,
        ]);

        $response = $this->actingAs($data['profesor'])
            ->put(route('entregas.calificar', $entrega), [
                'nota' => 85,
                'retroalimentacion' => 'Buen análisis',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('entregas', [
            'id' => $entrega->id,
            'nota' => '85.00',
            'retroalimentacion' => 'Buen análisis',
        ]);
    });

    it('rejects student from grading entrega', function () {
        $data = createTareaForStudent();
        $entrega = Entrega::factory()->create([
            'tarea_id' => $data['tarea']->id,
            'usuario_id' => $data['estudiante']->id,
        ]);

        $response = $this->actingAs($data['estudiante'])
            ->put(route('entregas.calificar', $entrega), [
                'nota' => 85,
                'retroalimentacion' => 'Buen análisis',
            ]);

        $response->assertForbidden();
    });

    it('validates nota range 0 to puntaje_maximo', function () {
        $data = createTareaForStudent();
        $entrega = Entrega::factory()->create([
            'tarea_id' => $data['tarea']->id,
            'usuario_id' => $data['estudiante']->id,
        ]);

        $response = $this->actingAs($data['profesor'])
            ->put(route('entregas.calificar', $entrega), [
                'nota' => 150,
                'retroalimentacion' => 'Test',
            ]);

        $response->assertSessionHasErrors('nota');
    });

    it('allows student to view own entrega detail', function () {
        Storage::fake('local');
        $data = createTareaForStudent();
        $entrega = Entrega::factory()->create([
            'tarea_id' => $data['tarea']->id,
            'usuario_id' => $data['estudiante']->id,
            'ruta_archivo' => 'tareas/'.$data['tarea']->id.'/'.$data['estudiante']->id.'_doc.pdf',
        ]);

        $response = $this->actingAs($data['estudiante'])
            ->get(route('entregas.show', $entrega));

        $response->assertStatus(200);
    });

    it('allows professor to view entrega detail', function () {
        Storage::fake('local');
        $data = createTareaForStudent();
        $entrega = Entrega::factory()->create([
            'tarea_id' => $data['tarea']->id,
            'usuario_id' => $data['estudiante']->id,
            'ruta_archivo' => 'tareas/'.$data['tarea']->id.'/'.$data['estudiante']->id.'_doc.pdf',
        ]);

        $response = $this->actingAs($data['profesor'])
            ->get(route('entregas.show', $entrega));

        $response->assertStatus(200);
    });

    it('allows downloading uploaded file', function () {
        Storage::fake('local');
        $data = createTareaForStudent();
        $entrega = Entrega::factory()->create([
            'tarea_id' => $data['tarea']->id,
            'usuario_id' => $data['estudiante']->id,
            'ruta_archivo' => 'tareas/'.$data['tarea']->id.'/'.$data['estudiante']->id.'_doc.pdf',
        ]);

        Storage::disk('local')->put($entrega->ruta_archivo, 'fake content');

        $response = $this->actingAs($data['profesor'])
            ->get(route('entregas.download', $entrega));

        $response->assertStatus(200);
    });

    it('rejects unauthenticated access to store', function () {
        $response = $this->post(route('entregas.store'), []);
        $response->assertRedirect('/login');
    });
});
