<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage A — CRM Completion
 *
 * Adds service_outcome to service_jobs.
 * Outcome augments the existing `status` column; it does NOT replace it.
 *
 * Supported values:
 *   completed_successfully | completed_with_followup_required | completed_partial
 *   cancelled_customer_request | cancelled_internal
 *   no_access | no_show | reschedule_required
 *   quote_required_after_visit | return_visit_required
 *   agreement_required_after_visit
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (! Schema::hasColumn('service_jobs', 'service_outcome')) {
                $table->string('service_outcome', 60)
                    ->nullable()
                    ->after('status')
                    ->comment('Structured outcome; augments status column');
                $table->index(['company_id', 'service_outcome'], 'sj_outcome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', static function (Blueprint $table) {
            if (Schema::hasColumn('service_jobs', 'service_outcome')) {
                $table->dropIndex('sj_outcome');
                $table->dropColumn('service_outcome');
            }
        });
    }
};
