<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module 4 — fieldservice_activity lifecycle completions
 *
 * Adds missing backend lifecycle columns to job_activities:
 *  - follow_up_at  — optional follow-up date/time for the activity
 *  - assigned_to   — user who owns / should action this activity
 *  - team_id       — team the activity is assigned to
 *
 * All new columns are nullable so existing rows are unaffected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_to')->nullable()->after('completed_by');
            $table->unsignedBigInteger('team_id')->nullable()->after('assigned_to');
            $table->timestamp('follow_up_at')->nullable()->after('completed_on');

            $table->foreign('assigned_to')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('team_id')
                ->references('id')->on('teams')
                ->onDelete('set null');

            $table->index(['company_id', 'assigned_to'], 'ja_assigned_to');
            $table->index(['company_id', 'follow_up_at'], 'ja_follow_up');
        });
    }

    public function down(): void
    {
        Schema::table('job_activities', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['team_id']);
            $table->dropIndex('ja_assigned_to');
            $table->dropIndex('ja_follow_up');
            $table->dropColumn(['assigned_to', 'team_id', 'follow_up_at']);
        });
    }
};
