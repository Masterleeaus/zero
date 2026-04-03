<?php

namespace Modules\FieldItems\Observers;

use App\Helper\Files;
use Modules\FieldItems\Entities\ItemFiles;

class ItemFileObserver
{

    public function saving(ItemFiles $itemFiles)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $itemFiles->last_updated_by = user()->id;
        }

    }

    public function creating(ItemFiles $itemFiles)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $itemFiles->added_by = user()->id;
        }

        if (company()) {
            $itemFiles->company_id = company()->id;
        }
    }

    public function deleting(ItemFiles $itemFiles)
    {
        $itemFiles->load('item');

        if (!isRunningInConsoleOrSeeding()) {
            if (isset($itemFiles->item) && $itemFiles->item->default_image == $itemFiles->hashname) {
                $itemFiles->item->default_image = null;
                $itemFiles->item->save();
            }
        }

        Files::deleteFile($itemFiles->hashname, ItemFiles::FILE_PATH);
    }

}
