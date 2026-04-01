<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_areas', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('name');
            $table->string('code')->nullable()->index();
            $table->string('description')->nullable();
            $table->string('type')->nullable()->comment('zip, suburb, state');
            $table->text('zip_codes')->nullable();
            $table->timestamps();

            $table->foreign('branch_id')
                ->references('id')
                ->on('service_area_branches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_areas');
    }
};
