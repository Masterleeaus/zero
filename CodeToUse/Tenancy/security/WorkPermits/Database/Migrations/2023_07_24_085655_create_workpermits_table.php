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
        Schema::create('tr_workpermits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->dateTime('date');
            $table->string('company_name');
            $table->string('company_address');
            $table->string('project_manj');
            $table->string('site_coor');
            $table->string('phone');
            $table->enum('jenis_pekerjaan', ['renovasi', 'non-renovasi']);
            $table->enum('lingkup_pekerjaan', ['interior', 'mechanical', 'electrical']);
            $table->unsignedInteger('unit_id')->nullable();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade')->onUpdate('cascade');
            $table->mediumText('detail_pekerjaan')->nullable();
            $table->date('date_start');
            $table->date('date_end');
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
            $table->mediumText('validated_remark')->nullable();
            $table->string('validated_img')->nullable();
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
        Schema::dropIfExists('tr_workpermits');
    }
};
