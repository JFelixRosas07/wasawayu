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
        Schema::create('detalles_rotacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('planes_rotacion')->onDelete('cascade');
            $table->integer('anio'); // 1,2,3,4...
            $table->foreignId('cultivo_id')->nullable()->constrained('cultivos')->onDelete('set null');
            $table->boolean('es_descanso')->default(false);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->text('alerta')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_rotacion');
    }
};
