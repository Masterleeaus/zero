<?php

namespace App\Observers;

use App\Events\FileUploadEvent;
use App\Models\ProjectFile;

class FileUploadObserver
{

    public function saving(ProjectFile $site)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $site->last_updated_by = user()->id;
        }
    }

    public function creating(ProjectFile $site)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $site->added_by = user()->id;
        }
    }

    public function created(ProjectFile $file)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new FileUploadEvent($file));
        }
    }

}
