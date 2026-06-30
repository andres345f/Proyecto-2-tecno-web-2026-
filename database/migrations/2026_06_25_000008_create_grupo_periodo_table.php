<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_periodo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->foreignId('periodo_academico_id')->constrained('periodos_academicos')->cascadeOnDelete();
            $table->foreignId('docente_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('cupo_maximo')->default(35);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['grupo_id', 'periodo_academico_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_periodo');
    }
};
