<?php

declare(strict_types=1);

namespace App\Models\Omni\Campaign;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * OmniCampaign — Multi-channel broadcast campaign.
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $company_id
 * @property int|null    $agent_id
 * @property int|null    $contact_list_id
 * @property string      $name
 * @property string      $channel_type
 * @property string|null $content
 * @property array|null  $content_variables
 * @property string      $status
 * @property \Carbon\Carbon|null $scheduled_at
 * @property \Carbon\Carbon|null $launched_at
 * @property \Carbon\Carbon|null $completed_at
 * @property int         $total_recipients
 * @property int         $sent_count
 * @property int         $delivered_count
 * @property int         $failed_count
 * @property array|null  $metadata
 */
class OmniCampaign extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_campaigns';

    protected $fillable = [
        'uuid',
        'company_id',
        'agent_id',
        'contact_list_id',
        'name',
        'channel_type',
        'content',
        'content_variables',
        'status',
        'scheduled_at',
        'launched_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'failed_count',
        'metadata',
    ];

    protected $casts = [
        'content_variables' => 'array',
        'scheduled_at'      => 'datetime',
        'launched_at'       => 'datetime',
        'completed_at'      => 'datetime',
        'metadata'          => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function contactList(): BelongsTo
    {
        return $this->belongsTo(OmniContactList::class, 'contact_list_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(OmniCampaignRecipient::class, 'campaign_id');
    }

    public function scopeDraft(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeRunning(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'running');
    }
}
