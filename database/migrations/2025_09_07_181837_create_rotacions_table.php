<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rotaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcela_id')->constrained('parcelas')->onDelete('cascade');
            $table->foreignId('cultivo_id')->constrained('cultivos')->onDelete('cascade');
            $table->date('fecha_siembra');
            $table->date('fecha_estim_cosecha')->nullable();
            $table->date('fecha_cosecha')->nullable();
            $table->enum('estado', ['planificado','en_proceso','finalizado'])->default('planificado');
            $table->text('manejo_suelo')->nullable();
            $table->foreignId('creado_por')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rotaciones');
    }
};
