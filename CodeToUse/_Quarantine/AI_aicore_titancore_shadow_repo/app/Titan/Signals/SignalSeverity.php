<?php

namespace App\Titan\Signals;

final class SignalSeverity
{
    public const GREEN = 'GREEN';
    public const AMBER = 'AMBER';
    public const RED   = 'RED';

    public static function all(): array
    {
        return [self::GREEN, self::AMBER, self::RED];
    }
}
