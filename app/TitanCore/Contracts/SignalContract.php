<?php

namespace App\TitanCore\Contracts;

/**
 * @deprecated Use App\Titan\Core\Contracts\SignalContract (canonical).
 *
 * This alias is retained so existing code referencing the TitanCore namespace
 * continues to resolve without import updates. New code must use the canonical path.
 */
interface SignalContract extends \App\Titan\Core\Contracts\SignalContract
{
    // Intentionally empty — the canonical interface carries all method signatures.
}
