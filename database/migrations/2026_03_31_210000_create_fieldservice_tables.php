<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_stages', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->string('stage_type')->default('order')->comment('order, location, worker, equipment');
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('fold')->default(false);
            $table->boolean('require_signature')->default(false);
            $table->string('color', 7)->default('#FFFFFF')->comment('Hex color');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'stage_type', 'sequence']);
        });

        Schema::create('job_types', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->timestamps();
            $table->index(['company_id', 'name']);
        });

        Schema::create('job_templates', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('job_type_id')->nullable()->index();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->string('name');
            $table->text('instructions')->nullable();
            $table->decimal('duration', 5, 2)->default(0)->comment('Default duration in hours');
            $table->timestamps();
        });

        Schema::create('service_job_workers', static function (Blueprint $table) {
            $table->unsignedBigInteger('service_job_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->primary(['service_job_id', 'user_id']);
        });

        Schema::create('site_workers', static function (Blueprint $table) {
            $table->unsignedBigInteger('site_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedSmallInteger('sequence')->default(10);
            $table->primary(['site_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_workers');
        Schema::dropIfExists('service_job_workers');
        Schema::dropIfExists('job_templates');
        Schema::dropIfExists('job_types');
        Schema::dropIfExists('job_stages');
    }
};
