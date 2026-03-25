<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tmovimdet', function (Blueprint $table) {
            $table->id();
            
            // Campos con longitudes ajustadas
            $table->char('movcoditip', 1)->nullable();
            $table->string('movnumero', 9)->nullable();
            $table->string('medcod', 10)->nullable();       // Cambiado de 5 a 10
            $table->string('medlote', 50)->nullable();      // Cambiado de 20 a 50
            $table->date('medfechvto')->nullable();
            $table->decimal('movcantid', 15, 2)->nullable(); // Cambiado de 12,2 a 15,2
            $table->decimal('movprecio', 15, 2)->nullable(); // Cambiado de 12,2 a 15,2
            $table->decimal('movtotal', 15, 2)->nullable();  // Cambiado de 12,2 a 15,2
            $table->dateTime('movfechult')->nullable();
            $table->char('movsitua', 1)->nullable();
            
            // Índices
            $table->index('movnumero', 'idx_tmovimdet_movnumero');
            $table->index('movcoditip', 'idx_tmovimdet_movcoditip');
            $table->index('medcod', 'idx_tmovimdet_medcod');
            $table->index('medlote', 'idx_tmovimdet_medlote');
            $table->index('movsitua', 'idx_tmovimdet_movsitua');
            $table->index('movfechult', 'idx_tmovimdet_movfechult');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tmovimdet');
    }
};