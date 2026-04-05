<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialActionRecommendation extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'action_type', 'title', 'summary', 'reason',
        'severity', 'confidence', 'source_service',
        'related_type', 'related_id', 'payload', 'status',
        'created_by_system', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected $casts = [
        'payload'           => 'array',
        'confidence'        => 'decimal:2',
        'reviewed_at'       => 'datetime',
        'created_by_system' => 'boolean',
    ];

    protected $attributes = [
        'status'            => 'pending_review',
        'created_by_system' => true,
        'severity'          => 'medium',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withDefault();
    }
}
