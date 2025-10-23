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
        Schema::create('planes_rotacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcela_id')
                ->constrained('parcelas')
                ->onDelete('restrict'); // Impide borrar una parcela en uso
            $table->string('nombre'); // Ej: Plan 2025-2029
            $table->integer('anios')->default(4);
            $table->foreignId('creado_por')->constrained('users');
            $table->enum('estado', ['planificado', 'en_ejecucion', 'finalizado'])->default('planificado');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_rotacion');
    }
};
