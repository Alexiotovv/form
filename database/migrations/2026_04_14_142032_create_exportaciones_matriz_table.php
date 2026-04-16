<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exportaciones_matriz', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->string('estado')->default('pendiente'); // pendiente, procesando, completado, error
            $table->integer('progreso')->default(0);
            $table->text('mensaje')->nullable();
            $table->text('filtros_aplicados')->nullable(); // Guardar filtros en JSON
            $table->integer('total_registros')->default(0);
            $table->integer('registros_procesados')->default(0);
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exportaciones_matriz');
    }
};