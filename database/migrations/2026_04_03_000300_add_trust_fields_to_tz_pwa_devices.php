<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds trust-hardening fields to tz_pwa_devices:
 *   - fingerprint            : deterministic node fingerprint hash
 *   - signing_key            : per-device HMAC signing key (nullable)
 *   - signature_failures     : running count of signature failures
 *   - last_failure_at        : timestamp of last signature failure
 *   - is_rate_limited        : flag indicating node is throttled
 *   - trust_notes            : operator notes on trust decisions
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tz_pwa_devices', function (Blueprint $table) {
            $table->string('fingerprint', 64)->nullable()->after('node_origin');
            $table->string('signing_key', 128)->nullable()->after('fingerprint');
            $table->unsignedSmallInteger('signature_failures')->default(0)->after('last_seen_at');
            $table->timestamp('last_failure_at')->nullable()->after('signature_failures');
            $table->boolean('is_rate_limited')->default(false)->after('last_failure_at');
            $table->text('trust_notes')->nullable()->after('is_rate_limited');

            $table->index('is_rate_limited');
            $table->index(['company_id', 'trust_level']);
        });
    }

    public function down(): void
    {
        Schema::table('tz_pwa_devices', function (Blueprint $table) {
            $table->dropIndex(['is_rate_limited']);
            $table->dropIndex(['company_id', 'trust_level']);
            $table->dropColumn([
                'fingerprint',
                'signing_key',
                'signature_failures',
                'last_failure_at',
                'is_rate_limited',
                'trust_notes',
            ]);
        });
    }
};
