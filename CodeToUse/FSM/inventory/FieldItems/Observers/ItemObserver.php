<?php

namespace Modules\FieldItems\Observers;

use Modules\FieldItems\Entities\Item;
use App\Traits\UnitTypeSaveTrait;

class ItemObserver
{

    use UnitTypeSaveTrait;

    public function saving(Item $item)
    {
        $this->unitType($item);

        if (!isRunningInConsoleOrSeeding()) {
            $item->last_updated_by = user() ? user()->id : null;
        }
    }

    public function creating(Item $item)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $item->added_by = user() ? user()->id : null;
        }

        if (company()) {
            $item->company_id = company()->id;
        }
    }

    public function deleting(Item $item)
    {
        $item->files()->each(function ($file) {
            $file->delete();
        });
    }

}
