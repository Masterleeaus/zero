<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PWA Pass 3 — Enhance tz_pwa_signal_ingress:
 *   - retry_count        : how many times promotion has been retried
 *   - deferred_until     : when a deferred item should next be retried
 *   - conflict_type      : type of conflict if detected (duplicate|timestamp_drift|consensus_fail|trust_override)
 *   - conflict_resolved_at : when the conflict was resolved
 *   - last_error_code    : machine-readable error code from last failure
 *   - server_received_at : server timestamp when the signal was received (for drift analysis)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tz_pwa_signal_ingress', function (Blueprint $table) {
            $table->unsignedTinyInteger('retry_count')->default(0)->after('failure_reason');
            $table->timestamp('deferred_until')->nullable()->after('retry_count');
            $table->string('conflict_type', 50)->nullable()->after('deferred_until');
            $table->timestamp('conflict_resolved_at')->nullable()->after('conflict_type');
            $table->string('last_error_code', 50)->nullable()->after('conflict_resolved_at');
            $table->timestamp('server_received_at')->nullable()->after('last_error_code');

            $table->index('deferred_until');
            $table->index(['company_id', 'conflict_type']);
            $table->index(['signal_stage', 'deferred_until']);
        });
    }

    public function down(): void
    {
        Schema::table('tz_pwa_signal_ingress', function (Blueprint $table) {
            $table->dropIndex(['deferred_until']);
            $table->dropIndex(['company_id', 'conflict_type']);
            $table->dropIndex(['signal_stage', 'deferred_until']);
            $table->dropColumn([
                'retry_count',
                'deferred_until',
                'conflict_type',
                'conflict_resolved_at',
                'last_error_code',
                'server_received_at',
            ]);
        });
    }
};
