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
        Schema::create('imed2', function (Blueprint $table) {
            $table->id();
            $table->string('annomes', 6); // Ejemplo: '202508'
            $table->string('codigo_eje', 20);
            $table->string('codigo_pre', 20);
            $table->date('fechdepo');
            $table->string('nrodepo', 50);
            $table->decimal('impdepo', 15, 2); // Monto con decimales
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imed2');
    }
};
