<?php

namespace Modules\Documents\Support;

class TenantResolver
{
    public static function id(): ?int
    {
        try {
            if (function_exists('company')) {
                $c = company();
                if ($c && isset($c->id)) {
                    return (int) $c->id;
                }
            }
        } catch (\Throwable $e) {
            // Never throw from tenant resolution.
        }

        try {
            $u = auth()->user();
            if ($u && isset($u->company_id)) {
                return (int) $u->company_id;
            }
        } catch (\Throwable $e) {
        }

        try {
            $id = auth()->id();
            return $id ? (int) $id : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
