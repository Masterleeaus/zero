<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class MarketingCampaign extends Model
{
    use \App\Traits\BelongsToStore;

    use HasFactory, LogsActivity;

    protected $fillable = ['store_id', 'name', 'type', 'message', 'recipients_count', 'status'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
