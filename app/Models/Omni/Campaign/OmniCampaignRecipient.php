<?php

declare(strict_types=1);

namespace App\Models\Omni\Campaign;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Omni\OmniCustomer;

/**
 * OmniCampaignRecipient — Per-recipient delivery evidence record.
 *
 * APPEND-ONLY for delivery status columns:
 *   sent_at, delivered_at, failed_at — set once, never overwritten.
 * No row deletion while campaign is active.
 *
 * @property int         $id
 * @property int         $campaign_id
 * @property int         $omni_customer_id
 * @property string|null $channel_address
 * @property string      $status
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon|null $delivered_at
 * @property \Carbon\Carbon|null $failed_at
 * @property string|null $failure_reason
 * @property string|null $external_message_id
 * @property array|null  $metadata
 */
class OmniCampaignRecipient extends Model
{
    protected $table = 'omni_campaign_recipients';

    protected $fillable = [
        'campaign_id',
        'omni_customer_id',
        'channel_address',
        'status',
        'sent_at',
        'delivered_at',
        'failed_at',
        'failure_reason',
        'external_message_id',
        'metadata',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at'    => 'datetime',
        'metadata'     => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(OmniCampaign::class, 'campaign_id');
    }

    public function omniCustomer(): BelongsTo
    {
        return $this->belongsTo(OmniCustomer::class, 'omni_customer_id');
    }
}
