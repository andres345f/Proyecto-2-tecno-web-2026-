<?php

use App\Models\OfertaAcademica;
use App\Models\User;
use App\Models\MatriculaCarrera;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('Matricula Carrera Bulk Import operations', function () {

    it('requires authentication to import matriculas', function () {
        $response = $this->post('/matriculas-carrera/importar', []);
        $response->assertRedirect('/login');
    });

    it('allows owner, director, or secretary to import matriculas', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        // Test with empty/invalid payload to see validation error instead of redirect to login
        $response = $this->post('/matriculas-carrera/importar', []);
        $response->assertSessionHasErrors(['archivo']);
    });

    it('imports student users and matriculas successfully from CSV', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'ING-SIS',
            'descripcion' => 'Carrera de sistemas',
            'resolucion_ministerial' => 'RM-101',
            'duración_semestres' => 10,
        ]);

        $csvContent = "nombre,email,codigo_oferta\n" .
                      "Carlos Perez,carlosp@example.com,ING-SIS\n" .
                      "Maria Gomez,mariag@example.com,ING-SIS";

        $file = UploadedFile::fake()->createWithContent('matriculas.csv', $csvContent);

        $response = $this->post('/matriculas-carrera/importar', [
            'archivo' => $file,
        ]);

        $response->assertRedirect('/matriculas-carrera');
        $response->assertSessionHas('success');

        // Check if users were created
        $student1 = User::where('email', 'carlosp@example.com')->first();
        $student2 = User::where('email', 'mariag@example.com')->first();

        expect($student1)->not->toBeNull();
        expect($student1->is_estudiante)->toBeTrue();
        expect(strlen($student1->codigo_estudiante))->toBe(9);
        // Verify default password is set to student code
        expect(Hash::check($student1->codigo_estudiante, $student1->password))->toBeTrue();

        expect($student2)->not->toBeNull();
        expect($student2->is_estudiante)->toBeTrue();
        expect(strlen($student2->codigo_estudiante))->toBe(9);
        expect(Hash::check($student2->codigo_estudiante, $student2->password))->toBeTrue();

        // Check if matriculas were created
        $this->assertDatabaseHas('matriculas_carrera', [
            'usuario_id' => $student1->id,
            'oferta_academica_id' => $oferta->id,
            'estado' => 'activo',
        ]);

        $this->assertDatabaseHas('matriculas_carrera', [
            'usuario_id' => $student2->id,
            'oferta_academica_id' => $oferta->id,
            'estado' => 'activo',
        ]);
    });

    it('handles UTF-8 BOM correctly in CSV header', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'ING-SIS',
            'descripcion' => 'Carrera de sistemas',
            'resolucion_ministerial' => 'RM-101',
            'duración_semestres' => 10,
        ]);

        $bom = pack('H*', 'EFBBBF');
        $csvContent = $bom . "nombre,email,codigo_oferta\n" .
                      "Carlos Perez,carlosp_bom@example.com,ING-SIS";

        $file = UploadedFile::fake()->createWithContent('matriculas_bom.csv', $csvContent);

        $response = $this->post('/matriculas-carrera/importar', [
            'archivo' => $file,
        ]);

        $response->assertRedirect('/matriculas-carrera');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'carlosp_bom@example.com',
            'is_estudiante' => true,
        ]);
    });

    it('rolls back transaction and returns detailed row errors on failure', function () {
        $user = User::factory()->create(['is_secretaria' => true]);
        $this->actingAs($user);

        $oferta = OfertaAcademica::create([
            'nombre' => 'Ingeniería de Sistemas',
            'codigo' => 'ING-SIS',
            'descripcion' => 'Carrera de sistemas',
            'resolucion_ministerial' => 'RM-101',
            'duración_semestres' => 10,
        ]);

        // Pre-create a user to trigger a duplicate email error on row 3
        User::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        $csvContent = "nombre,email,codigo_oferta\n" .
                      "Carlos Perez,carlosp@example.com,ING-SIS\n" . // Row 2 (this would be fine if not rolled back)
                      "Maria Gomez,duplicate@example.com,ING-SIS";   // Row 3 (duplicate email)

        $file = UploadedFile::fake()->createWithContent('matriculas_invalid.csv', $csvContent);

        $response = $this->post('/matriculas-carrera/importar', [
            'archivo' => $file,
        ]);

        // Should return errors and not redirect to index
        $response->assertSessionHasErrors(['import_errors']);
        
        $errors = session('errors')->getBag('default')->first('import_errors');
        expect($errors)->toContain("Fila 3: El correo 'duplicate@example.com' ya está registrado.");

        // Carlos Perez should NOT have been created due to database rollback
        $this->assertDatabaseMissing('users', [
            'email' => 'carlosp@example.com',
        ]);
    });
});
