<?php

namespace Modules\Contracts\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    protected $fillable = [
        'number','title','client_id','status','effective_date','expiry_date','notes',
        'current_version_id','subtotal','tax_total','grand_total'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(ContractVersion::class);
    }

    public function signers(): HasMany
    {
        return $this->hasMany(ContractSigner::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(SignatureEvent::class);
    }
}
