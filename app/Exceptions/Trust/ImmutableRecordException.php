<?php

declare(strict_types=1);

namespace App\Exceptions\Trust;

use RuntimeException;

class ImmutableRecordException extends RuntimeException
{
    public function __construct(string $message = 'Trust ledger entries are immutable and cannot be modified.')
    {
        parent::__construct($message);
    }
}
