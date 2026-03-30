<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleRecurringItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_recurring_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('schedule_recurring_id');
            $table->foreign('schedule_recurring_id')->references('id')->on('schedule_recurring')->onDelete('cascade')->onUpdate('cascade');
            $table->string('item_name')->nullable();
            $table->string('standar')->nullable();
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
        Schema::dropIfExists('schedule_recurring_items');
    }
}
