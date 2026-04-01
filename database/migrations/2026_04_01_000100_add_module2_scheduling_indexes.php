<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module 2 completion — add scheduling-focused composite indexes.
 *
 * These improve dispatch-board and calendar queries that filter by
 * company + status/stage + scheduled window simultaneously.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            // Dispatch-board: open jobs per company ordered by schedule
            $table->index(
                ['company_id', 'status', 'scheduled_date_start'],
                'sj_company_status_sched_start'
            );

            // Territory-aware assignment queries
            $table->index(
                ['company_id', 'territory_id', 'status'],
                'sj_company_territory_status'
            );

            // Priority queue for dispatcher
            $table->index(
                ['company_id', 'priority', 'scheduled_date_start'],
                'sj_company_priority_sched'
            );

            // Agreement scheduler: jobs linked to an agreement
            $table->index(
                ['agreement_id', 'scheduled_date_start'],
                'sj_agreement_sched'
            );
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropIndex('sj_company_status_sched_start');
            $table->dropIndex('sj_company_territory_status');
            $table->dropIndex('sj_company_priority_sched');
            $table->dropIndex('sj_agreement_sched');
        });
    }
};
