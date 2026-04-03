<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheet_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();

            $table->unsignedBigInteger('user_id')->index();
            $table->date('week_start')->index();
            $table->date('week_end')->index();

            $table->string('status', 30)->default('draft')->index(); // draft|submitted|approved|rejected
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable()->index();

            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->index();

            $table->text('submitter_notes')->nullable();
            $table->text('approver_notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->unique(['user_id', 'week_start'], 'timesheet_submissions_user_week_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_submissions');
    }
};
