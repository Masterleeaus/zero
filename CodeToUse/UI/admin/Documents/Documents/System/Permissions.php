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

            // Premium
            self::KEY_PREFIX.'templates.manage',
            self::KEY_PREFIX.'share',
            self::KEY_PREFIX.'export',

            self::KEY_PREFIX.'approve',
            self::KEY_PREFIX.'archive',
            self::KEY_PREFIX.'version',
            self::KEY_PREFIX.'restore',
            self::KEY_PREFIX.'link',

            // Tags
            self::KEY_PREFIX.'tags.manage',

            // Requests
            self::KEY_PREFIX.'requests.manage',
            self::KEY_PREFIX.'requests.send',

        ];
    }
}
