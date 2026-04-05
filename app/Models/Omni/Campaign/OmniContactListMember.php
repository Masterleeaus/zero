<?php

declare(strict_types=1);

namespace App\Models\Omni\Campaign;

use App\Models\Omni\OmniCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OmniContactListMember — Pivot record linking an OmniCustomer to an OmniContactList.
 *
 * Unique constraint: (contact_list_id, omni_customer_id) — enforced at DB level.
 *
 * @property int         $id
 * @property int         $contact_list_id
 * @property int         $omni_customer_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class OmniContactListMember extends Model
{
    protected $table = 'omni_contact_list_members';

    protected $fillable = [
        'contact_list_id',
        'omni_customer_id',
    ];

    public function contactList(): BelongsTo
    {
        return $this->belongsTo(OmniContactList::class, 'contact_list_id');
    }

    public function omniCustomer(): BelongsTo
    {
        return $this->belongsTo(OmniCustomer::class, 'omni_customer_id');
    }
}
