<?php

namespace Modules\Documents\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Documents\Entities\DocumentVersion;

class DocumentVersionCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public DocumentVersion $version)
    {
    }
}
