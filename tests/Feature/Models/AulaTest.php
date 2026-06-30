<?php

use App\Models\Aula;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Aula model', function () {

    it('has fillable attributes', function () {
        $aula = new Aula;

        expect($aula->getFillable())->toContain('nombre')
            ->and($aula->getFillable())->toContain('codigo')
            ->and($aula->getFillable())->toContain('capacidad');
    });

    it('casts attributes correctly', function () {
        $aula = Aula::factory()->create([
            'nombre' => 'Lab 101',
            'codigo' => 'LAB-101',
            'capacidad' => 30,
        ]);

        expect($aula->nombre)->toBe('Lab 101')
            ->and($aula->codigo)->toBe('LAB-101')
            ->and($aula->capacidad)->toBe(30);
    });

    it('uses soft deletes', function () {
        $aula = Aula::factory()->create();
        $aulaId = $aula->id;

        $aula->delete();

        $this->assertDatabaseHas('aulas', ['id' => $aulaId]);
        $this->assertSoftDeleted('aulas', ['id' => $aulaId]);
    });

    it('can be restored after soft delete', function () {
        $aula = Aula::factory()->create();
        $aula->delete();

        $aula->restore();

        $this->assertDatabaseHas('aulas', ['id' => $aula->id, 'deleted_at' => null]);
    });
});
