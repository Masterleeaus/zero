<?php

namespace Modules\Documents\Support;

class DocumentTypes
{
    public const GENERAL = 'general';
    public const SWMS = 'swms';
    public static function all(): array
    {
        return [self::GENERAL, self::SWMS];
    }
}
