<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('documents_tenant_id')) {
    /**
     * Documents module tenant scope key.
     *
     * Documents tables are tenant-scoped in your canonical schema (tenant_id).
     * We fall back to company_id if tenant_id is not present on the user model.
     */
    function documents_tenant_id(): ?int
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $tid = $user->tenant_id ?? null;
        if ($tid !== null) {
            return (int) $tid;
        }

        $cid = $user->company_id ?? null;
        return $cid !== null ? (int) $cid : null;
    }
}
