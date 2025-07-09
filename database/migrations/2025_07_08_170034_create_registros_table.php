<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registros', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('correo');
            $table->string('telefono');
            $table->date('fecha_envio');
            $table->time('hora_envio');
            $table->string('archivo');
            $table->unsignedBigInteger('profesion_id');
            $table->foreign('profesion_id')->references('id')->on('profesiones');
            $table->unsignedBigInteger('establecimiento_id');
            $table->foreign('establecimiento_id')->references('id')->on('establecimientos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros');
    }
};
