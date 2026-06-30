<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('malla_curricular', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oferta_academica_id')->constrained('ofertas_academicas')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->unsignedInteger('semestre_orden');
            $table->timestamps();

            // A materia can only appear once per career
            $table->unique(['oferta_academica_id', 'materia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('malla_curricular');
    }
};
