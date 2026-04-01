<?php

declare(strict_types=1);

namespace App\Models\Support;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceIssueMessage extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $guarded = [];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(ServiceIssue::class, 'service_issue_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
