<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenan_parkir_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('parkir_id')->nullable();
            $table->foreign('parkir_id')->references('id')->on('tenan_parkir')->onDelete('cascade')->onUpdate('cascade');
            $table->string('jenis_kendaraan');
            $table->string('jumlah_periode');
            $table->string('no_plat_lama');
            $table->string('no_plat_baru');
            $table->string('biaya');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenan_parkir_items');
    }
};
