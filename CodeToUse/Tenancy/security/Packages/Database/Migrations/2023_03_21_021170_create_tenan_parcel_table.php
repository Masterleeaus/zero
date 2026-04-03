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
        Schema::create('tr_package', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->date('tanggal_diterima');
            $table->unsignedInteger('ekspedisi_id')->nullable();
            $table->foreign('ekspedisi_id')->references('id')->on('tr_package_ekspedisi')->onDelete('cascade')->onUpdate('cascade');
            $table->string('no_hp_pengirim')->nullable();
            $table->string('nama_pengirim');
            $table->string('foto_penerima')->nullable();
            $table->string('jam');
            $table->string('catatan_penerima')->nullable();
            $table->enum('status_ambil', ['new', 'finished']);
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
        Schema::dropIfExists('tr_package');
    }
};
