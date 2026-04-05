<?php

declare(strict_types=1);

namespace App\Models\Omni;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

/**
 * OmniAnalytics — Aggregated conversation and channel metrics per company/agent/period.
 *
 * Populated by the SyncOmniAnalytics scheduled job (Pass 09).
 * One row per (company_id, agent_id, channel_type, period_date) combination.
 *
 * @property int         $id
 * @property int         $company_id
 * @property int|null    $agent_id
 * @property string|null $channel_type
 * @property \Carbon\Carbon $period_date
 * @property int         $conversations_opened
 * @property int         $conversations_resolved
 * @property int         $messages_sent
 * @property int         $messages_received
 * @property int         $avg_response_time_seconds
 * @property int         $voice_calls_total
 * @property int         $voice_calls_completed
 * @property int         $campaigns_launched
 * @property int         $campaign_messages_delivered
 * @property array|null  $metadata
 */
class OmniAnalytics extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_analytics';

    protected $fillable = [
        'company_id',
        'agent_id',
        'channel_type',
        'period_date',
        'conversations_opened',
        'conversations_resolved',
        'messages_sent',
        'messages_received',
        'avg_response_time_seconds',
        'voice_calls_total',
        'voice_calls_completed',
        'campaigns_launched',
        'campaign_messages_delivered',
        'metadata',
    ];

    protected $casts = [
        'period_date' => 'date',
        'metadata'    => 'array',
    ];
}
