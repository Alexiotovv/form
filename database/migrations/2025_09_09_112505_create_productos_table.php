<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('cod_unificado', 6)->nullable();
            $table->string('cod_sismed_analisis', 6)->nullable();
            $table->string('cod_sismed', 8)->nullable();
            $table->string('cod_siga',13)->nullable();
            $table->string('codigo_atc',4)->nullable();
            $table->string('cod_unspsc',10)->nullable();
            $table->string('descripcion_sismed',180)->nullable();
            $table->string('concentracion',50)->nullable();
            $table->string('forma_farmaceutica',50)->nullable();
            $table->string('presentacion',30)->nullable();
            $table->string('tipo_prod',1)->nullable();
            $table->string('lista_1',20)->nullable();
            $table->string('lista_2',20)->nullable();
            $table->string('tipo_abastecimiento',10)->nullable();
            $table->string('estrategico',1)->nullable();
            $table->string('biologicos',1)->nullable();
            $table->string('odontologicos',1)->nullable();
            $table->string('reactivos',1)->nullable();
            $table->string('vitales',5)->nullable();
            $table->string('peti2023',1)->nullable();
            $table->string('peti2018',1)->nullable();
            $table->string('peti2015',1)->nullable();
            $table->string('peti2012',1)->nullable();
            $table->string('peti2010',1)->nullable();
            $table->string('venta',1)->nullable();
            $table->string('estado',1)->nullable();
            $table->string('reg_sanit',50)->nullable();
            $table->string('descripcion_siga',200)->nullable();
            $table->string('descripcion_cubo',200)->nullable();
            $table->string('unidad_medida_x',10)->nullable();
            $table->string('descripcion_cubo_2',250)->nullable();
            $table->string('descripcion_producto',220)->nullable();
            $table->string('descripcion_producto_alt',220)->nullable();
            $table->string('descripcion_producto_eca',250)->nullable();
            $table->string('unidad_medida_siga',25)->nullable();
            $table->string('grupo',1)->nullable();
            $table->string('programas',50)->nullable();
            $table->string('programas_presupuestales',20)->nullable();
            $table->string('producto_fed',7)->nullable();
            $table->string('producto_fed_actual',10)->nullable();
            $table->string('tipo_indicador_fed',5)->nullable();
            $table->string('producto_ap_endis',10)->nullable();
            $table->string('anemia',10)->nullable();
            $table->string('claves_obstetricas',60)->nullable();
            $table->string('clave_azul',10)->nullable();
            $table->string('clave_amarilla',10)->nullable();
            $table->string('clave_roja',10)->nullable();
            $table->string('iras',20)->nullable();
            $table->string('iras_menor_12',15)->nullable();
            $table->string('edas',4)->nullable();
            $table->string('dengue',6)->nullable();
            $table->string('dengue_grupo_a',50)->nullable();
            $table->string('dengue_grupo_b',50)->nullable();
            $table->string('dengue_grupo_c',50)->nullable();
            $table->string('malaria',7)->nullable();
            $table->string('chikungunya',11)->nullable();
            $table->string('zika',4)->nullable();
            $table->string('leishmania',10)->nullable();
            $table->string('chagas',6)->nullable();
            $table->string('ofidismo',8)->nullable();
            $table->string('leptospirosis',13)->nullable();
            $table->string('planificacion_familiar',150)->nullable();
            $table->string('epp',5)->nullable();
            $table->string('covid19',8)->nullable();
            $table->string('covid19_apoyo_tto',15)->nullable();
            $table->string('covid_protocolo_minsa',30)->nullable();
            $table->string('pareto',1)->nullable();
            $table->string('vital',2)->nullable();
            $table->string('convenio_gestion_2020',5)->nullable();
            $table->string('convenio_gestion_2021',5)->nullable();
            $table->string('producto_cap_eca',7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('productos');
    }
};
