<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('matriculas_grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_periodo_id')->constrained('matriculas_periodo')->cascadeOnDelete();
            $table->foreignId('grupo_periodo_id')->constrained('grupo_periodo')->cascadeOnDelete();
            $table->decimal('nota_final', 5, 2)->nullable();
            $table->enum('estado', ['inscrito', 'en_curso', 'aprobado', 'reprobado', 'retirado'])->default('inscrito');
            $table->timestamps();

            $table->unique(['matricula_periodo_id', 'grupo_periodo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas_grupo');
    }
};
