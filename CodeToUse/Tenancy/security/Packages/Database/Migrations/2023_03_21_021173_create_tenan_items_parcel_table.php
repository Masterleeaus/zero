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
        Schema::create('tr_package_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('package_id')->nullable();
            $table->foreign('package_id')->references('id')->on('tr_package')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('unit_id')->nullable();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('type_id')->nullable();
            $table->foreign('type_id')->references('id')->on('tr_package_type')->onDelete('cascade')->onUpdate('cascade');
            $table->string('nama_penerima')->nullable();
            $table->enum('status_ambil', ['new', 'finished']);
            $table->string('nama_pengambil')->nullable();
            $table->string('no_hp_pengambil')->nullable();
            $table->string('id_card_pengambil')->nullable();
            $table->string('foto_pengambil')->nullable();
            $table->date('tanggal_pengambil')->nullable();
            $table->string('jam_ambil')->nullable();
            $table->string('catatan_pengambil')->nullable();
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
        Schema::dropIfExists('tr_package_items');
    }
};
