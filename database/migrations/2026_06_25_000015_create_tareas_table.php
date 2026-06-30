<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_periodo_id')->constrained('grupo_periodo')->cascadeOnDelete();
            $table->string('titulo', 255);
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_vencimiento');
            $table->decimal('puntaje_maximo', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
