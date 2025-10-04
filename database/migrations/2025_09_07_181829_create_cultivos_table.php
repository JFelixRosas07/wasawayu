<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cultivos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('categoria'); // tubérculo, leguminosa, cereal
            $table->enum('cargaSuelo', ['alta','media','baja']);
            $table->integer('diasCultivo');
            $table->string('epocaSiembra');
            $table->string('epocaCosecha');
            $table->text('descripcion')->nullable(); // nuevo
            $table->string('variedad')->nullable(); // nuevo
            $table->text('recomendaciones')->nullable();
            $table->string('imagen')->nullable(); // ruta de la imagen
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cultivos');
    }
};
