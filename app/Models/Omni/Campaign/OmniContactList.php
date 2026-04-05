<?php

declare(strict_types=1);

namespace App\Models\Omni\Campaign;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Omni\OmniCustomer;

/**
 * OmniContactList — Named group of OmniCustomers for campaign targeting.
 *
 * @property int         $id
 * @property int         $company_id
 * @property string      $name
 * @property string|null $description
 * @property int         $member_count
 * @property array|null  $metadata
 */
class OmniContactList extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_contact_lists';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'member_count',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(
            OmniCustomer::class,
            'omni_contact_list_members',
            'contact_list_id',
            'omni_customer_id'
        );
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(OmniCampaign::class, 'contact_list_id');
    }
}
