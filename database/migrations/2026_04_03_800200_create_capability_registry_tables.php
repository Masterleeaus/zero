<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Skill Definitions ────────────────────────────────────────────────
        Schema::create('skill_definitions', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->boolean('requires_certification')->default(false);
            $table->unsignedSmallInteger('expiry_months')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->unique(['company_id', 'name']);
        });

        // ── Technician Skills ────────────────────────────────────────────────
        Schema::create('technician_skills', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('skill_definition_id')->index();
            $table->enum('level', ['trainee', 'competent', 'proficient', 'expert'])->default('trainee');
            $table->date('acquired_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->unsignedBigInteger('endorsed_by')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'skill_definition_id']);
            $table->index(['user_id', 'expires_at']);
            $table->unique(['user_id', 'skill_definition_id']);
        });

        // ── Certifications ───────────────────────────────────────────────────
        Schema::create('certifications', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('certification_name');
            $table->string('issuing_body')->nullable();
            $table->string('certificate_number')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('document_path')->nullable();
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['company_id', 'expires_at']);
            $table->index(['company_id', 'status']);
        });

        // ── Skill Requirements (per JobType) ─────────────────────────────────
        Schema::create('skill_requirements', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_type_id')->index();
            $table->unsignedBigInteger('skill_definition_id')->index();
            $table->enum('minimum_level', ['trainee', 'competent', 'proficient', 'expert'])->default('competent');
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();

            $table->unique(['job_type_id', 'skill_definition_id']);
        });

        // ── Availability Windows (recurring weekly) ──────────────────────────
        Schema::create('availability_windows', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sunday … 6=Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'day_of_week', 'is_active']);
        });

        // ── Availability Overrides (date-specific exceptions) ────────────────
        Schema::create('availability_overrides', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->date('date')->index();
            $table->boolean('available')->default(false);
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_overrides');
        Schema::dropIfExists('availability_windows');
        Schema::dropIfExists('skill_requirements');
        Schema::dropIfExists('certifications');
        Schema::dropIfExists('technician_skills');
        Schema::dropIfExists('skill_definitions');
    }
};
