<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas_periodo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_carrera_id')->constrained('matriculas_carrera');
            $table->foreignId('periodo_academico_id')->constrained('periodos_academicos');
            $table->foreignId('plan_pago_id')->constrained('planes_pago');
            $table->datetime('fecha_matricula');
            $table->enum('estado', ['activo', 'inactivo', 'completado'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas_periodo');
    }
};
