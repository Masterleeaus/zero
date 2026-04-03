<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documents_tags')) {
            Schema::create('documents_tags', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tenant_id')->index();
                $table->string('name');
                $table->string('slug')->index();
                $table->string('bg_color')->nullable();
                $table->string('text_color')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'slug']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('documents_tags')) {
            Schema::drop('documents_tags');
        }
    }
};
