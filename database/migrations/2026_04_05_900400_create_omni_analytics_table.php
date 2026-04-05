<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TITAN OMNI — Pass 02 — Analytics table
 *
 * Creates:
 *   omni_analytics — Aggregated conversation/channel metrics per company/agent/period
 *
 * Donor source: CodeToUse/Voice/TitanOmni Complete Pass26 HARDENED
 * Populated by scheduled SyncOmniAnalytics job (Pass 09).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('omni_analytics')) {
            Schema::create('omni_analytics', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('agent_id')->nullable()->index();
                $table->string('channel_type', 50)->nullable()->index();
                $table->date('period_date')->index();
                $table->unsignedInteger('conversations_opened')->default(0);
                $table->unsignedInteger('conversations_resolved')->default(0);
                $table->unsignedInteger('messages_sent')->default(0);
                $table->unsignedInteger('messages_received')->default(0);
                $table->unsignedInteger('avg_response_time_seconds')->default(0);
                $table->unsignedInteger('voice_calls_total')->default(0);
                $table->unsignedInteger('voice_calls_completed')->default(0);
                $table->unsignedInteger('campaigns_launched')->default(0);
                $table->unsignedInteger('campaign_messages_delivered')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'agent_id', 'channel_type', 'period_date'], 'omni_analytics_unique_period');
                $table->index(['company_id', 'period_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('omni_analytics');
    }
};
