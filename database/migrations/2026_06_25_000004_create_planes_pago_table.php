<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('planes_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oferta_academica_id')->constrained('ofertas_academicas')->cascadeOnDelete();
            $table->string('nombre', 255);
            $table->enum('tipo', ['unico', 'por_periodo', 'especial']);
            $table->decimal('monto_matricula', 10, 2);
            $table->decimal('monto_cuota', 10, 2);
            $table->integer('cantidad_cuotas');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_pago');
    }
};
