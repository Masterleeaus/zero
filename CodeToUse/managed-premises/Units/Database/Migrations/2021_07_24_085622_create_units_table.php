<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('unit_code');
            $table->string('unit_name')->nullable();
            $table->unsignedInteger('floor_id')->unsigned()->nullable();
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('tower_id')->unsigned()->nullable();
            $table->foreign('tower_id')->references('id')->on('towers')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('typeunit_id')->unsigned()->nullable();
            $table->foreign('typeunit_id')->references('id')->on('type_units')->onDelete('cascade')->onUpdate('cascade');
            $table->decimal('luas', 10, 3)->default(0);
            $table->text('address')->nullable();
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
        Schema::dropIfExists('units');
    }
}
