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
        Schema::create('tr_in_out_permit', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('identity', ['ktp', 'sim', 'kitas', 'others'])->default('ktp');
            $table->string('identity_number');
            $table->enum('pembawa_brg', ['penghuni', 'kontraktor', 'supplier']);
            $table->date('date');
            $table->time('jam');
            $table->string('pj');
            $table->string('no_hp');
            $table->string('name');
            $table->unsignedInteger('unit_id')->nullable();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('keterangan', ['in', 'out', 'transfer']);
            $table->string('jenis_barang');
            $table->boolean('status_approve')->default(false);
            $table->unsignedInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->dateTime('approved_at')->nullable();
            $table->boolean('status_approve_bm')->default(false);
            $table->unsignedInteger('approved_bm')->nullable();
            $table->foreign('approved_bm')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->dateTime('approved_bm_at')->nullable();
            $table->boolean('status_validated')->default(false);
            $table->unsignedInteger('validated_by')->nullable();
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->dateTime('validated_at')->nullable();
            $table->string('validate_img')->nullable();
            $table->mediumText('validate_remark')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('tr_in_out_permit');
    }
};
