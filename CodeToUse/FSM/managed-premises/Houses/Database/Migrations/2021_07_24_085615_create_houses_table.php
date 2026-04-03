<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('houses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('house_code');
            $table->string('house_name')->nullable();
            $table->unsignedInteger('area_id')->unsigned()->nullable();
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('tower_id')->unsigned()->nullable();
            $table->foreign('tower_id')->references('id')->on('towers')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('typehouse_id')->unsigned()->nullable();
            $table->foreign('typehouse_id')->references('id')->on('type_houses')->onDelete('cascade')->onUpdate('cascade');
            $table->decimal('luas', 10, 3)->default(0);
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
        Schema::dropIfExists('houses');
    }
}
