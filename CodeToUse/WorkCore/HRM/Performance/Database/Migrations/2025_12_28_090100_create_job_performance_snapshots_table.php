<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_performance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('objective_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // tech / worker
            $table->unsignedBigInteger('supervisor_id')->nullable()->index();
            $table->unsignedBigInteger('project_id')->nullable()->index(); // fallback "site/job" for Worksuite
            $table->unsignedBigInteger('job_id')->nullable()->index();
            $table->unsignedBigInteger('jobsite_id')->nullable()->index();
            $table->unsignedBigInteger('work_order_id')->nullable()->index();

            $table->decimal('overall_score', 5, 2)->nullable();
            $table->decimal('quality_score', 5, 2)->nullable();
            $table->decimal('safety_score', 5, 2)->nullable();
            $table->decimal('timeliness_score', 5, 2)->nullable();
            $table->decimal('documentation_score', 5, 2)->nullable();

            $table->unsignedInteger('callback_count')->default(0);
            $table->decimal('customer_rating', 3, 2)->nullable(); // 1.00–5.00

            $table->string('status', 32)->default('draft'); // draft|signed_off
            $table->timestamp('signed_off_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_performance_snapshots');
    }
};
