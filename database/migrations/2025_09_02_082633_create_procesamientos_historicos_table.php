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
        Schema::create('procesamientos_historicos', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_ejecucion'); // fecha de la ejecución
            $table->text('tiempo_ejecucion'); // en segundos o milisegundos
            $table->text('tablas_registros')->nullable(); // para guardar en formato JSON
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // relación con usuarios
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesamientos_historicos');
    }
};
