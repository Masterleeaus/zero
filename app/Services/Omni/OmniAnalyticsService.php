<?php

declare(strict_types=1);

namespace App\Services\Omni;

use App\Models\Omni\OmniAnalytics;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * OmniAnalyticsService — channel analytics aggregation and reporting.
 *
 * Provides methods to record incremental counters (called from event
 * listeners / jobs) and read period reports. The actual aggregation job
 * (SyncOmniAnalytics) will be implemented in Pass 09.
 */
class OmniAnalyticsService
{
    /**
     * Increment a specific counter for a company/agent/channel/day.
     *
     * @param  array<string, mixed>  $dimensions  company_id, agent_id (nullable), channel_type (nullable), period_date
     */
    public function increment(array $dimensions, string $metric, int $by = 1): void
    {
        $periodDate = $dimensions['period_date'] ?? now()->toDateString();

        $record = OmniAnalytics::withoutGlobalScope('company')
            ->where('company_id', $dimensions['company_id'])
            ->where('agent_id', $dimensions['agent_id'] ?? null)
            ->where('channel_type', $dimensions['channel_type'] ?? null)
            ->whereDate('period_date', $periodDate)
            ->first();

        if ($record) {
            $record->increment($metric, $by);
        } else {
            OmniAnalytics::create([
                'company_id'  => $dimensions['company_id'],
                'agent_id'    => $dimensions['agent_id'] ?? null,
                'channel_type' => $dimensions['channel_type'] ?? null,
                'period_date' => $periodDate,
                $metric       => $by,
            ]);
        }
    }

    /**
     * Retrieve analytics rows for a company within a date range.
     *
     * @return Collection<int, OmniAnalytics>
     */
    public function periodReport(int $companyId, Carbon $from, Carbon $to, ?int $agentId = null): Collection
    {
        return OmniAnalytics::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->whereBetween('period_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('period_date')
            ->get();
    }

    /**
     * Summarise totals across all channels for a company in a date range.
     *
     * @return array<string, int>
     */
    public function summary(int $companyId, Carbon $from, Carbon $to): array
    {
        $rows = $this->periodReport($companyId, $from, $to);

        return [
            'conversations_opened'       => $rows->sum('conversations_opened'),
            'conversations_resolved'     => $rows->sum('conversations_resolved'),
            'messages_sent'              => $rows->sum('messages_sent'),
            'messages_received'          => $rows->sum('messages_received'),
            'voice_calls_total'          => $rows->sum('voice_calls_total'),
            'voice_calls_completed'      => $rows->sum('voice_calls_completed'),
            'campaigns_launched'         => $rows->sum('campaigns_launched'),
            'campaign_messages_delivered' => $rows->sum('campaign_messages_delivered'),
        ];
    }
}
