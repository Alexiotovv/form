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
        Schema::create('form_det', function (Blueprint $table) {
            $table->id();
            $table->string('CODIGO_EJE')->nullable();
            $table->string('CODIGO_PRE')->nullable();
            $table->string('TIPSUM')->nullable();
            $table->string('ANNOMES')->nullable();
            $table->string('CODIGO_MED')->nullable();
            $table->float('SALDO')->nullable();
            $table->float('PRECIO')->nullable();
            $table->float('INGRE')->nullable();
            $table->float('REINGRE')->nullable();
            $table->float('VENTA')->nullable();
            $table->float('SIS')->nullable();
            $table->float('INTERSAN')->nullable();
            $table->float('FAC_PERD')->nullable();
            $table->float('DEFNAC')->nullable();
            $table->float('EXO')->nullable();
            $table->float('SOAT')->nullable();
            $table->float('CREDHOSP')->nullable();
            $table->float('OTR_CONV')->nullable();
            $table->float('DEVOL')->nullable();
            $table->float('VENCIDO')->nullable();
            $table->float('MERMA')->nullable();
            $table->float('DISTRI')->nullable();
            $table->float('TRANSF')->nullable();
            $table->float('VENTAINST')->nullable();
            $table->float('DEV_VEN')->nullable();
            $table->float('DEV_MERMA')->nullable();
            $table->float('OTRAS_SAL')->nullable();
            $table->float('STOCK_FIN')->nullable();
            $table->float('STOCK_FIN1')->nullable();
            $table->float('REQ')->nullable();
            $table->float('TOTAL')->nullable();
            $table->date('FEC_EXP')->nullable();
            $table->float('DO_SALDO')->nullable();
            $table->float('DO_INGRE')->nullable();
            $table->float('DO_CON')->nullable();
            $table->float('DO_OTR')->nullable();
            $table->float('DO_TOT')->nullable();
            $table->float('DO_STK')->nullable();
            $table->date('DO_FECEXP')->nullable();
            $table->date('FECHA')->nullable();
            $table->string('USUARIO')->nullable();
            $table->string('INDIPROC')->nullable();
            $table->string('SIT')->nullable();
            $table->string('INDISIGA')->nullable();
            $table->string('DSTKCERO')->nullable();
            $table->float('MPTOREPO')->nullable();
            $table->float('ING_REGULA')->nullable();
            $table->float('SAL_REGULA')->nullable();
            $table->float('SAL_CONINS')->nullable();
            $table->float('STOCKFIN')->nullable();
            $table->float('STOCKFIN1')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_det');
    }
};
