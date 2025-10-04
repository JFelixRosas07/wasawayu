<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcelas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->float('extension'); // en hectáreas
            $table->string('ubicacion'); // antes: ubicacionTextual
            $table->string('tipoSuelo'); // franco, arcilloso, arenoso, etc.
            $table->string('usoSuelo'); // agrícola, descanso, pastoreo, etc.
            $table->json('poligono'); // coordenadas en formato GeoJSON
            $table->foreignId('agricultor_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcelas');
    }
};
