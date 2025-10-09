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
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->Integer('cod_pliego')->nullable();
            $table->string('pliego',255)->nullable()->default('');
            $table->Integer('cod_disa')->nullable();
            $table->string('disa_diresa',255)->default('')->default('');
            $table->Integer('cod_ue_mef')->nullable();
            $table->string('ue_mef')->nullable()->default('');
            $table->string('departamento',50)->nullable()->default('');
            $table->string('ubigeo',25)->nullable()->default('');
            $table->string('provincia',50)->nullable()->default('');
            $table->string('distrito',50)->nullable()->default('');
            $table->string('cod_renipress')->nullable()->default('');
            $table->string('cod_ipress',10)->default('');
            $table->string('red')->nullable()->default('');
            $table->string('microred')->nullable()->default('');
            $table->string('nombre_ipress',100)->nullable()->default('');
            $table->string('codigo_nombre_ipress',100)->default('');
            $table->string('nivel',10)->nullable()->default('');
            $table->string('tipo_establecimiento',50)->nullable()->default('');
            $table->string('estado_ipress',10)->nullable()->default('');
            $table->string('universo_ipress',2)->nullable()->default('');
            $table->string('ipress_feed',2)->nullable()->default('');
            $table->string('ipress_eca',2)->nullable()->default('');
            $table->string('ipress_evaluar_disponibilidad',2)->nullable()->default('');
            $table->string('ipress_dengue',7)->nullable()->default('');
            $table->string('ipress_prio_temp_bajas',2)->nullable()->default('');
            $table->string('ipress_prio_riesg_lluv',15)->nullable()->default('');
            $table->string('est_pert_cuencas',10)->nullable()->default('');
            $table->string('ipress_prio_plan_malaria',2)->nullable()->default('');
            $table->string('almacen_pertenece',50)->nullable()->default('');
            $table->string('filtro',6)->nullable()->default('');
            $table->string('ruta_distribucion',7)->nullable()->default('');
            $table->string('monitor',100)->nullable()->default('');
            $table->string('digitador',100)->nullable()->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacenes');
    }
};
