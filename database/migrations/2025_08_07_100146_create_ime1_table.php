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
        Schema::create('ime1', function (Blueprint $table) {
            $table->id();
              $table->string('ANNOMES', 6);
            $table->string('CODIGO_EJE', 10);
            $table->string('CODIGO_PRE', 20);

            $table->decimal('IMPVTAS', 15, 5)->nullable()->default(0);
            $table->decimal('IMPCREDH', 15, 5)->nullable()->default(0);
            $table->decimal('IMPSOAT', 15, 5)->nullable()->default(0);
            $table->decimal('IMPOTRC', 15, 5)->nullable()->default(0);
            $table->decimal('IMPSIS', 15, 5)->nullable()->default(0);
            $table->decimal('IMPINTS', 15, 5)->nullable()->default(0);
            $table->decimal('IMPDN', 15, 5)->nullable()->default(0);
            $table->decimal('IMPEXO', 15, 5)->nullable()->default(0);

            $table->decimal('DVTAS80', 15, 5)->nullable()->default(0);
            $table->decimal('DVTAS20', 15, 5)->nullable()->default(0);
            $table->decimal('DCREH80', 15, 5)->nullable()->default(0);
            $table->decimal('DCREH20', 15, 5)->nullable()->default(0);
            $table->decimal('DSOAT80', 15, 5)->nullable()->default(0);
            $table->decimal('DSOAT20', 15, 5)->nullable()->default(0);
            $table->decimal('DOTRC80', 15, 5)->nullable()->default(0);
            $table->decimal('DOTRC20', 15, 5)->nullable()->default(0);
            $table->decimal('TOTRDR', 15, 5)->nullable()->default(0);
            $table->decimal('DSIS80', 15, 5)->nullable()->default(0);
            $table->decimal('DSIS20', 15, 5)->nullable()->default(0);
            $table->decimal('DINTSAN80', 15, 5)->nullable()->default(0);
            $table->decimal('DINTSAN20', 15, 5)->nullable()->default(0);
            $table->decimal('DDN80', 15, 5)->nullable()->default(0);
            $table->decimal('DDN20', 15, 5)->nullable()->default(0);

            $table->decimal('CTCREDH', 15, 5)->nullable()->default(0);
            $table->decimal('CTSOAT', 15, 5)->nullable()->default(0);
            $table->decimal('CTOCONV', 15, 5)->nullable()->default(0);
            $table->decimal('CTSIS', 15, 5)->nullable()->default(0);
            $table->decimal('CTISAN', 15, 5)->nullable()->default(0);
            $table->decimal('CTDN', 15, 5)->nullable()->default(0);
            $table->decimal('SALMEDA', 15, 5)->nullable()->default(0);
            $table->decimal('FORT110', 15, 5)->nullable()->default(0);
            $table->decimal('EXO1', 15, 5)->nullable()->default(0);
            $table->decimal('ABMEDIN', 15, 5)->nullable()->default(0);
            $table->decimal('SMEDSIG', 15, 5)->nullable()->default(0);
            $table->decimal('SADMA', 15, 5)->nullable()->default(0);
            $table->decimal('FORT010', 15, 5)->nullable()->default(0);
            $table->decimal('EXO0', 15, 5)->nullable()->default(0);
            $table->decimal('GASTADM', 15, 5)->nullable()->default(0);
            $table->decimal('SADMSIG', 15, 5)->nullable()->default(0);

            $table->string('BVTASER1', 50)->nullable()->nullable();
            $table->string('BVTADEL1', 50)->nullable()->nullable();
            $table->string('BVTAAL1', 50)->nullable()->nullable();
            $table->decimal('BVTAAN1', 15, 5)->nullable()->default(0);

            $table->string('BVTASER2', 50)->nullable();
            $table->string('BVTADEL2', 50)->nullable();
            $table->string('BVTAAL2', 50)->nullable();
            $table->decimal('BVTAAN2', 15, 5)->nullable()->default(0);

            $table->string('BVTASER3', 50)->nullable();
            $table->string('BVTADEL3', 50)->nullable();
            $table->string('BVTAAL3', 50)->nullable();
            $table->decimal('BVTAAN3', 15, 5)->nullable()->default(0);

            $table->string('FACTSER', 50)->nullable();
            $table->string('FACTDEL', 50)->nullable();
            $table->string('FACTAL', 50)->nullable();
            $table->decimal('FACTAN', 15, 5)->nullable()->default(0);

            $table->decimal('IMPBVVTA', 15, 5)->nullable()->default(0);
            $table->decimal('IMPBVSIS', 15, 5)->nullable()->default(0);
            $table->decimal('IMPBVEXO', 15, 5)->nullable()->default(0);
            $table->decimal('IMPBVSOAT', 15, 5)->nullable()->default(0);
            $table->decimal('IMPBVOCONV', 15, 5)->nullable()->default(0);
            $table->decimal('IMPBVINTSA', 15, 5)->nullable()->default(0);
            $table->decimal('IMPBVDN', 15, 5)->nullable()->default(0);

            $table->date('FECHREG')->nullable();
            $table->date('FECHULTM')->nullable();
            $table->string('USUARIO', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ime1');
    }
};
