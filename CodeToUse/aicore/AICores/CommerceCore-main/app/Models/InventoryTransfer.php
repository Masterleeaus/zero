<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;

class InventoryTransfer extends Model
{
    use \App\Traits\BelongsToStore;

    use LogsActivity;
    protected $fillable = ['product_id', 'from_branch_id', 'to_branch_id', 'quantity', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }
}
