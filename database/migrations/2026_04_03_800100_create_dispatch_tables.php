<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dispatch_assignments')) {
            Schema::create('dispatch_assignments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('job_id')->index();
                $table->unsignedBigInteger('technician_id')->index();
                $table->string('assigned_by')->default('ai'); // user|ai
                $table->decimal('constraint_score', 5, 2)->nullable();
                $table->unsignedInteger('travel_estimate_mins')->nullable();
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->string('status')->default('pending'); // pending|confirmed|declined|superseded
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('dispatch_constraints')) {
            Schema::create('dispatch_constraints', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('company_id')->index();
                $table->string('constraint_type'); // skill|territory|availability|sla|travel_cost
                $table->decimal('weight', 5, 2)->default(1.0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('dispatch_queue')) {
            Schema::create('dispatch_queue', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('job_id')->unique();
                $table->decimal('priority_score', 8, 2)->default(0);
                $table->timestamp('queued_at')->nullable();
                $table->unsignedInteger('attempts')->default(0);
                $table->timestamp('last_attempt_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatch_queue');
        Schema::dropIfExists('dispatch_constraints');
        Schema::dropIfExists('dispatch_assignments');
    }
};
