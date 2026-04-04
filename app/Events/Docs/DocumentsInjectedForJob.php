<?php

declare(strict_types=1);

namespace App\Events\Docs;

use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DocumentsInjectedForJob
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly Collection $injectedDocuments,
    ) {}
}
