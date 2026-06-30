<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_propietario')->default(false);
            $table->boolean('is_director')->default(false);
            $table->boolean('is_secretaria')->default(false);
            $table->boolean('is_profesor')->default(false);
            $table->boolean('is_estudiante')->default(false);
            $table->string('codigo_estudiante')->nullable();
            $table->boolean('is_activo')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_propietario',
                'is_director',
                'is_secretaria',
                'is_profesor',
                'is_estudiante',
                'is_activo',
            ]);
        });
    }
};
