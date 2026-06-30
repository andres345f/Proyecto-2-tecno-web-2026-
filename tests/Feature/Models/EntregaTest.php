<?php

use App\Models\Entrega;
use App\Models\Tarea;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('Entrega Model', function () {

    it('has correct table name', function () {
        $entrega = new Entrega;
        expect($entrega->getTable())->toBe('entregas');
    });

    it('has correct fillable attributes', function () {
        $entrega = new Entrega;
        expect($entrega->getFillable())->toBe([
            'tarea_id',
            'usuario_id',
            'ruta_archivo',
            'fecha_entrega',
            'nota',
            'retroalimentacion',
        ]);
    });

    it('belongs to a tarea', function () {
        $entrega = Entrega::factory()->create();

        expect($entrega->tarea)->toBeInstanceOf(Tarea::class);
    });

    it('belongs to a usuario', function () {
        $entrega = Entrega::factory()->create();

        expect($entrega->usuario)->toBeInstanceOf(User::class);
    });

    it('casts fecha_entrega to datetime', function () {
        $entrega = Entrega::factory()->create([
            'fecha_entrega' => '2026-03-10 14:30:00',
        ]);

        expect($entrega->fecha_entrega)->toBeInstanceOf(Carbon::class);
    });

    it('casts nota to decimal', function () {
        $entrega = Entrega::factory()->create(['nota' => 85.50]);

        expect($entrega->nota)->toBe('85.50');
    });

    it('returns archivo_url accessor', function () {
        Storage::fake('local');

        $entrega = Entrega::factory()->create([
            'ruta_archivo' => 'tareas/1/1_documento.pdf',
        ]);

        expect($entrega->archivo_url)->toContain('tareas/1/1_documento.pdf');
    });

    it('returns null archivo_url when no ruta_archivo', function () {
        $entrega = Entrega::factory()->create([
            'ruta_archivo' => null,
        ]);

        expect($entrega->archivo_url)->toBeNull();
    });
});
