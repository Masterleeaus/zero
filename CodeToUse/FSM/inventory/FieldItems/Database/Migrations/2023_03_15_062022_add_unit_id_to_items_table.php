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

        if (!Schema::hasColumn('items', 'unit_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->bigInteger('unit_id')->unsigned()->nullable()->default(null);
                $table->foreign('unit_id')
                    ->references('id')
                    ->on('unit_types')
                    ->onDelete('SET NULL')
                    ->onUpdate('cascade');
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
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
