<?php

declare(strict_types=1);

namespace App\Models\Trust;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

/**
 * A periodic cryptographic seal of the chain state for a company.
 *
 * Sealing captures a root hash derived from all chain hashes up to that point,
 * giving an immutable checkpoint that can be used to detect later tampering.
 */
class TrustChainSeal extends Model
{
    use BelongsToCompany;

    protected $table = 'trust_chain_seals';

    protected $fillable = [
        'company_id',
        'sealed_at',
        'entry_count',
        'root_hash',
        'seal_hash',
        'sealed_by',
    ];

    protected $casts = [
        'sealed_at'   => 'datetime',
        'entry_count' => 'integer',
    ];
}
