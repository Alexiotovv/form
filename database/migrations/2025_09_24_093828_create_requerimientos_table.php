<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequerimientosTable extends Migration
{
    public function up()
    {
        Schema::create('requerimientos', function (Blueprint $table) {
            $table->id();
            $table->string('cod_sismed'); // COD_SISMED
            $table->text('descripcion_producto'); // DESCRIPCIÓN DEL PRODUCTO
            $table->integer('stock_final'); // STOCK_FINAL
            $table->date('fecha_venc')->nullable(); // FECHA_VENC
            $table->integer('cpm'); // CPM
            $table->decimal('msd', 8, 2)->nullable(); // MSD (por ahora nullable)
            $table->string('situacion_stock'); // SITUACION_STOCK
            $table->string('situacion_fecha_venc')->nullable(); // SITUACIÓN FECHA_VENC.
            $table->integer('req_sugerido')->nullable(); // REQ. SUGERIDO
            $table->integer('req_final')->nullable(); // REQ. FINAL (lo ingresa el usuario)

            $table->unsignedBigInteger('almacen_id')->nullable(); // Relación opcional con almacén
            $table->foreign('almacen_id')->references('id')->on('almacenes')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('requerimientos');
    }
}