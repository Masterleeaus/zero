<?php

namespace Modules\Documents\Support;

class StatusHelper
{
    public static function label(string $status): string
    {
        return match ($status) {
            'draft' => __('Draft'),
            'review' => __('In review'),
            'approved' => __('Approved'),
            'archived' => __('Archived'),
            default => ucfirst($status),
        };
    }
}
