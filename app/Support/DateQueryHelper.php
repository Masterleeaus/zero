<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DateQueryHelper
{
    public static function monthExpression(string $column): string
    {
        if (! preg_match('/^[A-Za-z0-9_\\.]+$/', $column)) {
            throw new InvalidArgumentException('Invalid column name provided for month expression.');
        }

        return match (DB::getDriverName()) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }
}
