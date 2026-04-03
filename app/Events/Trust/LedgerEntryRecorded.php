<?php

declare(strict_types=1);

namespace App\Events\Trust;

use App\Models\Trust\TrustLedgerEntry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LedgerEntryRecorded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly TrustLedgerEntry $entry) {}
}
