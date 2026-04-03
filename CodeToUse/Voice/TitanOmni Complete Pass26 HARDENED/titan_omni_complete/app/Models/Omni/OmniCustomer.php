<?php

namespace App\Models\Omni;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OmniCustomer extends Model
{
    use HasFactory;

    protected $table = 'omni_customers';

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'external_ref',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
    ];

    public function conversations(): HasMany
    {
        return $this->hasMany(OmniConversation::class, 'customer_id');
    }
}
