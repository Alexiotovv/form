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
        Schema::create('unidades_ejecutoras', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('ejecutora');
            $table->string('pliego');
            $table->string('sector');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades_ejecutoras');
    }
};
