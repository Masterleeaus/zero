<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class Expense extends Model
{
    use \App\Traits\BelongsToStore;

    use LogsActivity;
    protected $fillable = ['store_id', 'category', 'amount', 'description', 'date'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
