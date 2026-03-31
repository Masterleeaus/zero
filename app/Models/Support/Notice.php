<?php

declare(strict_types=1);

namespace App\Models\Support;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notice extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $fillable = [
        'company_id',
        'created_by',
        'title',
        'body',
        'type',
        'is_pinned',
        'publish_at',
        'expire_at',
    ];

    protected $attributes = [
        'type'      => 'info',
        'is_pinned' => false,
    ];

    protected $casts = [
        'is_pinned'  => 'boolean',
        'publish_at' => 'datetime',
        'expire_at'  => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function views(): HasMany
    {
        return $this->hasMany(NoticeView::class);
    }
}
