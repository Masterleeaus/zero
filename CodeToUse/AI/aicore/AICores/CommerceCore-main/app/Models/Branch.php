<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class Branch extends Model
{
    use \App\Traits\BelongsToStore;

    use LogsActivity;
    protected $fillable = ['store_id', 'name', 'address', 'phone', 'email', 'is_primary'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function inventories()
    {
        return $this->hasMany(BranchInventory::class);
    }
}
