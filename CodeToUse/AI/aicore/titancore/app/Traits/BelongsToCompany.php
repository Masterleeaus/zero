<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Concerns\BelongsToCompany as ScopedBelongsToCompany;

/**
 * @deprecated Use App\Models\Concerns\BelongsToCompany directly.
 */
trait BelongsToCompany
{
    use ScopedBelongsToCompany;
}
