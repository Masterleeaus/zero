<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheet_timers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index();

            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->unsignedBigInteger('task_id')->nullable()->index();
            $table->unsignedBigInteger('work_order_id')->nullable()->index();

            $table->string('status', 30)->default('running')->index(); // running|stopped|converted
            $table->timestamp('started_at')->index();
            $table->timestamp('stopped_at')->nullable()->index();

            $table->integer('seconds_total')->default(0);
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_timers');
    }
};
