<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oferta_academica_id')->constrained('ofertas_academicas')->cascadeOnDelete();
            $table->string('nombre', 255);
            $table->string('tipo', 50); // semestral, anual
            $table->date('fecha_inicio_inscripcion')->nullable();
            $table->date('fecha_fin_inscripcion')->nullable();
            $table->date('fecha_inicio_cierre')->nullable();
            $table->date('fecha_fin_cierre')->nullable();
            $table->date('fecha_inicio_retiro')->nullable();
            $table->integer('numero_maximo_materias')->nullable();
            $table->enum('estado', ['inscripcion', 'cierre', 'retiro', 'terminado'])->default('inscripcion');
            $table->date('fecha_fin_retiro')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_academicos');
    }
};
