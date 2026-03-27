<?php

namespace App\Models;

use App\Models\Concerns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use Concerns\BelongsToCompany;

    protected $table = 'products';

    protected $fillable = [
        'name', 'type', 'description', 'key_features', 'user_id', 'company_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
