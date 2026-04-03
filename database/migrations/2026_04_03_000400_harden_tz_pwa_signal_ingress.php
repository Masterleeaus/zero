<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hardens tz_pwa_signal_ingress with:
 *   - idempotency_key  : client-supplied deduplication key
 *   - failure_reason   : last failure message for debugging
 *   - ingest_status    : coarse status: accepted | rejected | duplicate | deferred | invalid
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tz_pwa_signal_ingress', function (Blueprint $table) {
            $table->string('idempotency_key', 128)->nullable()->after('node_id');
            $table->string('ingest_status', 30)->default('accepted')->after('signal_stage');
            $table->string('failure_reason', 500)->nullable()->after('ingest_status');

            $table->unique(['node_id', 'idempotency_key'], 'unique_pwa_ingress_idempotency');
            $table->index('ingest_status');
            $table->index(['company_id', 'signal_stage']);
            $table->index('processed_at');
        });
    }

    public function down(): void
    {
        Schema::table('tz_pwa_signal_ingress', function (Blueprint $table) {
            $table->dropUnique('unique_pwa_ingress_idempotency');
            $table->dropIndex(['ingest_status']);
            $table->dropIndex(['company_id', 'signal_stage']);
            $table->dropIndex(['processed_at']);
            $table->dropColumn(['idempotency_key', 'ingest_status', 'failure_reason']);
        });
    }
};
