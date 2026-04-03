<?php

declare(strict_types=1);

namespace App\Events\Team;

use App\Models\Team\Certification;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CertificationRevoked
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Certification $certification) {}
}
