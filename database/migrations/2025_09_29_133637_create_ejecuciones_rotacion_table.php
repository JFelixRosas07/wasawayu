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
        Schema::create('ejecuciones_rotacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detalle_id')->constrained('detalles_rotacion')->onDelete('cascade');
            $table->date('fecha_siembra');
            $table->date('fecha_cosecha')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['en_proceso', 'finalizado'])->default('en_proceso');
            $table->foreignId('creado_por')->constrained('users');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ejecuciones_rotacion');
    }
};
