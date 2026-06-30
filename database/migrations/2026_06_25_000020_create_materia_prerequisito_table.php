<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materia_prerequisito', function (Blueprint $table) {
            $table->id();
            
            // Relación de Malla a Malla
            $table->foreignId('malla_curricular_id')->constrained('malla_curricular')->cascadeOnDelete();
            $table->foreignId('prerequisito_malla_id')->constrained('malla_curricular')->cascadeOnDelete();

            // Índice único para evitar duplicar el mismo prerrequisito
            $table->unique(['malla_curricular_id', 'prerequisito_malla_id'], 'malla_prereq_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materia_prerequisito');
    }
};
