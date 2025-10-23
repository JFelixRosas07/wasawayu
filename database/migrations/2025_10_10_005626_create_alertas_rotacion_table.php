<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlertasRotacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alertas_rotacion', function (Blueprint $table) {
            $table->id();

            // FK al detalle de rotación (se eliminarán las alertas si se borra el detalle)
            $table->foreignId('detalle_rotacion_id')
                  ->constrained('detalles_rotacion')
                  ->onDelete('cascade');

            $table->string('tipo_alerta', 120);
            $table->text('descripcion')->nullable();

            $table->enum('severidad', ['baja', 'media', 'alta'])->default('media');
            $table->enum('estado', ['activa', 'resuelta'])->default('activa');

            $table->dateTime('fecha_generada')->useCurrent();
            $table->dateTime('fecha_resuelta')->nullable();

            $table->timestamps();

            // Índices útiles
            $table->index('tipo_alerta');
            $table->index('severidad');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alertas_rotacion');
    }
}
