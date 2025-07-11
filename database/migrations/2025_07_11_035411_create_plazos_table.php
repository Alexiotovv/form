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
        Schema::create('plazos', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('dia_inicio');   // 1 → 31
            $table->unsignedTinyInteger('dia_fin');      // 1 → 31
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plazos');
    }
};
