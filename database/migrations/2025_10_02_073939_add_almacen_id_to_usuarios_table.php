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
        Schema::table('users', function (Blueprint $table) {
             $table->unsignedBigInteger('almacen_id')->nullable();

            // Crea la clave foránea
            $table->foreign('almacen_id')
                  ->references('id')
                  ->on('almacenes')
                  ->onDelete('set null'); // o 'cascade'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Elimina la FK primero
            $table->dropForeign(['almacen_id']);
            // Luego elimina la columna
            $table->dropColumn('almacen_id');
        });
    }
};
