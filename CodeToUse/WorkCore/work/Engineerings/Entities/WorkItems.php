<?php
namespace Modules\Engineerings\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Modules\Items\Entities\Item;

class WorkItems extends BaseModel
{
    use HasCompany;

    protected $table = 'workrequests_items';
    protected $guarded = ['id'];

    public function item()
    {
        return $this->belongsTo(Item::class, 'items_id');
    }
}

