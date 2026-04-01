<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('regions', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('manager_user_id')->nullable()->index();
            $table->timestamps();
            $table->index(['company_id', 'name']);
        });

        Schema::create('districts', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('region_id')->nullable()->index();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('manager_user_id')->nullable()->index();
            $table->timestamps();
            $table->index(['company_id', 'region_id']);
        });

        Schema::create('branches', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('district_id')->nullable()->index();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('manager_user_id')->nullable()->index();
            $table->timestamps();
            $table->index(['company_id', 'district_id']);
        });

        Schema::create('territories', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type')->nullable()->comment('zip, state, country');
            $table->text('zip_codes')->nullable()->comment('Comma-separated ZIP/postcode list');
            $table->timestamps();
            $table->index(['company_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('territories');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regions');
    }
};
