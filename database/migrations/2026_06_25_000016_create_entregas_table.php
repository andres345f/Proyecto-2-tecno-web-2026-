<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained('tareas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->string('ruta_archivo')->nullable();
            $table->dateTime('fecha_entrega');
            $table->decimal('nota', 5, 2)->nullable();
            $table->text('retroalimentacion')->nullable();
            $table->timestamps();

            $table->unique(['tarea_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};
