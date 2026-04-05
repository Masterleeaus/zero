<?php

declare(strict_types=1);

namespace App\Models\Omni;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * OmniCustomer — Omni-side channel identity.
 *
 * Bridges to the host CRM Customer via nullable crm_customer_id.
 * Omni does NOT copy customer address, notes, or deal data.
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $company_id
 * @property int|null    $crm_customer_id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $phone
 * @property array|null  $channel_identities
 * @property string|null $external_ref
 * @property array|null  $tags
 * @property array|null  $metadata
 */
class OmniCustomer extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_customers';

    protected $fillable = [
        'uuid',
        'company_id',
        'crm_customer_id',
        'name',
        'email',
        'phone',
        'channel_identities',
        'external_ref',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'channel_identities' => 'array',
        'tags'               => 'array',
        'metadata'           => 'array',
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

    public function conversations(): HasMany
    {
        return $this->hasMany(OmniConversation::class, 'omni_customer_id');
    }

    public function voiceCalls(): HasMany
    {
        return $this->hasMany(Voice\OmniVoiceCall::class, 'omni_customer_id');
    }

    public function callbackSchedules(): HasMany
    {
        return $this->hasMany(Voice\OmniCallbackSchedule::class, 'omni_customer_id');
    }
}
