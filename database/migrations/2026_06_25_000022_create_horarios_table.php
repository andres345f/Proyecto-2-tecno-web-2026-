<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_periodo_id')->constrained('grupo_periodo')->cascadeOnDelete();
            $table->string('dia'); // Lunes, Martes, Miércoles, Jueves, Viernes, Sábado, Domingo
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->foreignId('aula_id')->constrained('aulas')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
