<?php

use App\Models\GrupoPeriodo;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createProfessorWithGrupo(): array
{
    $profesor = User::factory()->create(['is_profesor' => true]);
    $grupoPeriodo = GrupoPeriodo::factory()->create(['docente_id' => $profesor->id]);

    return compact('profesor', 'grupoPeriodo');
}

function createStudentEnrolledInGrupo(): array
{
    $data = createProfessorWithGrupo();
    $estudiante = User::factory()->create(['is_estudiante' => true]);

    return [...$data, 'estudiante' => $estudiante];
}

describe('Tarea CRUD', function () {

    it('allows professor to create tarea for own grupo', function () {
        $data = createProfessorWithGrupo();

        $response = $this->actingAs($data['profesor'])
            ->post(route('grupos.tareas.store', $data['grupoPeriodo']), [
                'titulo' => 'Parcial 1',
                'descripcion' => 'Primer parcial del semestre',
                'fecha_vencimiento' => now()->addWeek()->format('Y-m-d H:i:s'),
                'puntaje_maximo' => 100,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tareas', [
            'grupo_periodo_id' => $data['grupoPeriodo']->id,
            'titulo' => 'Parcial 1',
            'puntaje_maximo' => 100,
        ]);
    });

    it('rejects student from creating tarea', function () {
        $data = createStudentEnrolledInGrupo();

        $response = $this->actingAs($data['estudiante'])
            ->post(route('grupos.tareas.store', $data['grupoPeriodo']), [
                'titulo' => 'Parcial 1',
                'descripcion' => 'Primer parcial',
                'fecha_vencimiento' => now()->addWeek()->format('Y-m-d H:i:s'),
                'puntaje_maximo' => 100,
            ]);

        $response->assertForbidden();
    });

    it('rejects professor from creating tarea in grupo they do not teach', function () {
        $profesor = User::factory()->create(['is_profesor' => true]);
        $otroProfesor = User::factory()->create(['is_profesor' => true]);
        $grupoPeriodo = GrupoPeriodo::factory()->create(['docente_id' => $otroProfesor->id]);

        $response = $this->actingAs($profesor)
            ->post(route('grupos.tareas.store', $grupoPeriodo), [
                'titulo' => 'Parcial 1',
                'descripcion' => 'Primer parcial',
                'fecha_vencimiento' => now()->addWeek()->format('Y-m-d H:i:s'),
                'puntaje_maximo' => 100,
            ]);

        $response->assertForbidden();
    });

    it('lists tareas for a grupo', function () {
        $data = createProfessorWithGrupo();
        Tarea::factory()->count(3)->create(['grupo_periodo_id' => $data['grupoPeriodo']->id]);

        $response = $this->actingAs($data['profesor'])
            ->get(route('grupos.tareas.index', $data['grupoPeriodo']));

        $response->assertStatus(200);
    });

    it('shows tarea detail', function () {
        $data = createProfessorWithGrupo();
        $tarea = Tarea::factory()->create(['grupo_periodo_id' => $data['grupoPeriodo']->id]);

        $response = $this->actingAs($data['profesor'])
            ->get(route('grupos.tareas.show', [$data['grupoPeriodo'], $tarea]));

        $response->assertStatus(200);
    });

    it('allows professor to update own tarea', function () {
        $data = createProfessorWithGrupo();
        $tarea = Tarea::factory()->create(['grupo_periodo_id' => $data['grupoPeriodo']->id]);

        $response = $this->actingAs($data['profesor'])
            ->put(route('grupos.tareas.update', [$data['grupoPeriodo'], $tarea]), [
                'titulo' => 'Parcial 1 Modificado',
                'descripcion' => 'Descripcion actualizada',
                'fecha_vencimiento' => now()->addMonth()->format('Y-m-d H:i:s'),
                'puntaje_maximo' => 50,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tareas', [
            'id' => $tarea->id,
            'titulo' => 'Parcial 1 Modificado',
            'puntaje_maximo' => 50,
        ]);
    });

    it('allows professor to delete own tarea', function () {
        $data = createProfessorWithGrupo();
        $tarea = Tarea::factory()->create(['grupo_periodo_id' => $data['grupoPeriodo']->id]);

        $response = $this->actingAs($data['profesor'])
            ->delete(route('grupos.tareas.destroy', [$data['grupoPeriodo'], $tarea]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('tareas', ['id' => $tarea->id]);
    });

    it('validates required fields on store', function () {
        $data = createProfessorWithGrupo();

        $response = $this->actingAs($data['profesor'])
            ->post(route('grupos.tareas.store', $data['grupoPeriodo']), []);

        $response->assertSessionHasErrors(['titulo', 'fecha_vencimiento', 'puntaje_maximo']);
    });

    it('validates titulo max length', function () {
        $data = createProfessorWithGrupo();

        $response = $this->actingAs($data['profesor'])
            ->post(route('grupos.tareas.store', $data['grupoPeriodo']), [
                'titulo' => str_repeat('a', 256),
                'fecha_vencimiento' => now()->addWeek()->format('Y-m-d H:i:s'),
                'puntaje_maximo' => 100,
            ]);

        $response->assertSessionHasErrors('titulo');
    });

    it('validates fecha_vencimiento must be in the future', function () {
        $data = createProfessorWithGrupo();

        $response = $this->actingAs($data['profesor'])
            ->post(route('grupos.tareas.store', $data['grupoPeriodo']), [
                'titulo' => 'Parcial 1',
                'fecha_vencimiento' => now()->subDay()->format('Y-m-d H:i:s'),
                'puntaje_maximo' => 100,
            ]);

        $response->assertSessionHasErrors('fecha_vencimiento');
    });

    it('validates puntaje_maximo min and max', function () {
        $data = createProfessorWithGrupo();

        $response = $this->actingAs($data['profesor'])
            ->post(route('grupos.tareas.store', $data['grupoPeriodo']), [
                'titulo' => 'Parcial 1',
                'fecha_vencimiento' => now()->addWeek()->format('Y-m-d H:i:s'),
                'puntaje_maximo' => 0,
            ]);

        $response->assertSessionHasErrors('puntaje_maximo');
    });

    it('rejects unauthenticated access', function () {
        $grupoPeriodo = GrupoPeriodo::factory()->create();

        $response = $this->get(route('grupos.tareas.index', $grupoPeriodo));
        $response->assertRedirect('/login');
    });
});
