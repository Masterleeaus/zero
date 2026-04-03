<?php

namespace Modules\Documents\Support;

class DocumentStatus
{
    public const DRAFT = 'draft';
    public const REVIEW = 'review';
    public const APPROVED = 'approved';
    public const ARCHIVED = 'archived';

    public static function all(): array
    {
        return [self::DRAFT, self::REVIEW, self::APPROVED, self::ARCHIVED];
    }
}
