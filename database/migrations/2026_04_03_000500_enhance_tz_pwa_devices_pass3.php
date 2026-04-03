<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PWA Pass 3 — Enhance tz_pwa_devices:
 *   - capability_profile  : JSON device capability snapshot
 *   - runtime_version     : PWA runtime version reported by device
 *   - last_sync_at        : timestamp of last successful sync
 *   - last_success_at     : timestamp of last successfully promoted signal
 *   - trust_demoted_at    : timestamp of last trust demotion
 *   - capability_tier     : classified tier (mobile_light / mobile_standard / tablet_standard / desktop_full)
 *   - queue_backlog       : last-known pending signal count on device
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tz_pwa_devices', function (Blueprint $table) {
            $table->json('capability_profile')->nullable()->after('meta_json');
            $table->string('capability_tier', 30)->nullable()->after('capability_profile');
            $table->string('runtime_version', 20)->nullable()->after('capability_tier');
            $table->timestamp('last_sync_at')->nullable()->after('last_seen_at');
            $table->timestamp('last_success_at')->nullable()->after('last_sync_at');
            $table->timestamp('trust_demoted_at')->nullable()->after('last_failure_at');
            $table->unsignedSmallInteger('queue_backlog')->default(0)->after('trust_demoted_at');

            $table->index('capability_tier');
            $table->index(['company_id', 'last_sync_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tz_pwa_devices', function (Blueprint $table) {
            $table->dropIndex(['capability_tier']);
            $table->dropIndex(['company_id', 'last_sync_at']);
            $table->dropColumn([
                'capability_profile',
                'capability_tier',
                'runtime_version',
                'last_sync_at',
                'last_success_at',
                'trust_demoted_at',
                'queue_backlog',
            ]);
        });
    }
};
