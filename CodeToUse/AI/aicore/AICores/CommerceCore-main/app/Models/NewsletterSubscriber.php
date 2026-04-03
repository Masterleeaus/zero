<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use \App\Traits\BelongsToStore;

    protected $fillable = ['store_id', 'email', 'first_name', 'last_name', 'status'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
