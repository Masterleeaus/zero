<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('predictions')) {
            Schema::create('predictions', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('prediction_type')->comment('asset_failure|sla_breach|demand_surge|capacity_gap|maintenance_overdue|inspection_due');
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->decimal('confidence_score', 5, 4)->default(0.0000);
                $table->timestamp('predicted_at')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->string('status')->default('active')->comment('active|triggered|expired|dismissed');
                $table->text('recommended_action')->nullable();
                $table->json('explanation_trace')->nullable();
                $table->string('model_provider')->nullable();
                $table->string('model_id')->nullable();
                $table->unsignedBigInteger('dismissed_by')->nullable();
                $table->timestamp('dismissed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['company_id', 'status']);
                $table->index(['company_id', 'prediction_type']);
                $table->index(['subject_type', 'subject_id']);
                $table->index('expires_at');
            });
        }

        if (! Schema::hasTable('prediction_signals')) {
            Schema::create('prediction_signals', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('prediction_id')->index();
                $table->string('signal_type');
                $table->string('signal_source_type')->nullable();
                $table->unsignedBigInteger('signal_source_id')->nullable();
                $table->json('signal_value')->nullable();
                $table->decimal('weight', 5, 4)->default(0.0000);
                $table->timestamp('recorded_at')->nullable();
                $table->timestamps();

                $table->foreign('prediction_id')->references('id')->on('predictions')->cascadeOnDelete();
                $table->index(['signal_source_type', 'signal_source_id']);
            });
        }

        if (! Schema::hasTable('prediction_outcomes')) {
            Schema::create('prediction_outcomes', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('prediction_id')->unique();
                $table->boolean('outcome_occurred')->default(false);
                $table->timestamp('outcome_at')->nullable();
                $table->float('variance_hours')->nullable();
                $table->text('feedback_notes')->nullable();
                $table->unsignedBigInteger('recorded_by')->nullable();
                $table->timestamps();

                $table->foreign('prediction_id')->references('id')->on('predictions')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('prediction_schedules')) {
            Schema::create('prediction_schedules', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('prediction_type');
                $table->unsignedSmallInteger('frequency_hours')->default(24);
                $table->timestamp('last_run_at')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('config')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'prediction_type']);
                $table->index(['is_active', 'next_run_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_schedules');
        Schema::dropIfExists('prediction_outcomes');
        Schema::dropIfExists('prediction_signals');
        Schema::dropIfExists('predictions');
    }
};
