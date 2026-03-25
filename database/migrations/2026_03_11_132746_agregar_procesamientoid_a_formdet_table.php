<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('form_det', function (Blueprint $table) {
            $table->foreignId('procesamiento_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('procesamientos_historicos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('form_det', function (Blueprint $table) {
            $table->dropForeign(['procesamiento_id']);
            $table->dropColumn('procesamiento_id');
        });
    }
};