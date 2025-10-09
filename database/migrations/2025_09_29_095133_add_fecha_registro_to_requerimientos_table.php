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
        Schema::table('requerimientos', function (Blueprint $table) {
            $table->date('fecha_registro')->nullable()->after('almacen_id'); // o el campo que prefieras como referencia
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requerimientos', function (Blueprint $table) {
            $table->dropColumn('fecha_registro');
        });
    }
};
