<?php
namespace Modules\PropertyManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\PropertyManagement\Entities\PropertyDocument;

class PropertyDocumentUploaded
{
    use Dispatchable, SerializesModels;

    public function __construct(public PropertyDocument $document) {}
}
