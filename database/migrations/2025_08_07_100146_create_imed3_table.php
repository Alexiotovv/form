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
        Schema::create('imed3', function (Blueprint $table) {
            $table->id();
            $table->string('ANNOMES', 20);         // Ej: 202507
            $table->string('CODIGO_EJE', 20);      // Ej: 024
            $table->string('CODIGO_PRE', 20);      // Ej: 00142
            $table->date('FECHGUIA');             // Ej: 06/12/25 -> formatear a Y-m-d al insertar
            $table->string('NROGUIA', 20);        // Ej: 0008218
            $table->decimal('IMPGUIA', 10, 5);    // Ej: 63.23000
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imed3');
    }
};
