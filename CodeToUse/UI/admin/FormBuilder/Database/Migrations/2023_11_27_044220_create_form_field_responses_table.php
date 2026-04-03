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
        if(!Schema::hasTable('form_field_responses'))
        {
            Schema::create('form_field_responses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('form_id');
                $table->integer('subject_id');
                $table->integer('name_id');
                $table->integer('email_id');
                $table->integer('user_id');
                $table->integer('pipeline_id');
                $table->integer('workspace')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_field_responses');
    }
};
