<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas_carrera', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('oferta_academica_id')->constrained('ofertas_academicas');
            $table->datetime('fecha_matricula');
            $table->enum('estado', ['activo', 'inactivo', 'retirado'])->default('activo');
            $table->timestamps();

            $table->unique(['usuario_id', 'oferta_academica_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas_carrera');
    }
};
