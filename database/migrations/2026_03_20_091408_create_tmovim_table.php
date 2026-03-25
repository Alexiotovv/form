<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tmovim', function (Blueprint $table) {
            $table->id();
            
            // Campos con longitudes ajustadas según datos reales
            $table->char('movcoditip', 1)->nullable();
            $table->string('movnumero', 9)->nullable();
            $table->string('almcodiorg', 10)->nullable();  // Cambiado de 5 a 10
            $table->string('almorgvir', 10)->nullable();   // Cambiado de 8 a 10
            $table->string('almcodidst', 10)->nullable();  // Cambiado de 10 a 10 (se mantiene)
            $table->string('almdstvir', 10)->nullable();   // Cambiado de 10 a 10 (se mantiene)
            $table->char('movtipodci', 2)->nullable();
            $table->char('movnumedci', 8)->nullable();
            $table->char('movtipodco', 2)->nullable();
            $table->string('movnumedco', 10)->nullable();
            $table->char('cctcodigo', 2)->nullable();
            $table->decimal('movtot', 15, 2)->nullable();  // Cambiado de 12,2 a 15,2 para números más grandes
            $table->string('prvnumeruc', 20)->nullable();   // Cambiado de 12 a 20
            $table->string('prvdescrip', 200)->nullable();  // Cambiado de 100 a 200
            $table->string('movrefe', 200)->nullable();     // Cambiado de 150 a 200
            $table->dateTime('movfechult')->nullable();
            $table->char('movsitua', 1)->nullable();
            $table->char('tip_comp', 1)->nullable();
            $table->char('tip_proc', 2)->nullable();
            $table->string('num_proc', 100)->nullable();
            $table->string('movfecanul', 30)->nullable();
            
            // Índices
            $table->index('movnumero', 'idx_tmovim_movnumero');
            $table->index('movcoditip', 'idx_tmovim_movcoditip');
            $table->index('movsitua', 'idx_tmovim_movsitua');
            $table->index('movfechult', 'idx_tmovim_movfechult');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tmovim');
    }
};