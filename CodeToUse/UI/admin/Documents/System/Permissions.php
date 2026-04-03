<?php

namespace Modules\Documents\System;

class Permissions
{
    public const KEY_PREFIX = 'documents.';

    public static function all(): array
    {
        return [
            self::KEY_PREFIX.'view',
            self::KEY_PREFIX.'create',
            self::KEY_PREFIX.'update',
            self::KEY_PREFIX.'delete',
            self::KEY_PREFIX.'templates.manage',
            self::KEY_PREFIX.'share',
        ];
    }
}
