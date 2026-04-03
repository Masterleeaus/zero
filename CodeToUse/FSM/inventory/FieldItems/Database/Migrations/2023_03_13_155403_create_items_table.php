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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name');
            $table->string('price');
            $table->string('taxes')->nullable();
            $table->boolean('allow_purchase')->default(false);
            $table->boolean('downloadable')->default(false);
            $table->string('downloadable_file')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id')->nullable()->index('items_category_id_foreign');
            $table->unsignedBigInteger('sub_category_id')->nullable()->index('items_sub_category_id_foreign');
            $table->unsignedInteger('added_by')->nullable()->index('items_added_by_foreign');
            $table->unsignedInteger('last_updated_by')->nullable()->index('items_last_updated_by_foreign');
            $table->foreign(['added_by'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['category_id'])->references(['id'])->on('item_category')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['last_updated_by'])->references(['id'])->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['sub_category_id'])->references(['id'])->on('item_sub_category')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->string('hsn_sac_code')->nullable();
            $table->string('default_image')->nullable();
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
        Schema::dropIfExists('items');
    }
};
