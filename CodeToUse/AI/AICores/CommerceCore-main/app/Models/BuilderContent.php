<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuilderContent extends Model
{
    use \App\Traits\BelongsToStore;

    protected $fillable = ['store_id', 'section_id', 'key', 'value'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(BuilderSection::class, 'section_id');
    }
}
