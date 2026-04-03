<?php

if (! function_exists('tenant')) {
    function tenant(): ?int
    {
        if (! function_exists('auth')) {
            return null;
        }

        $user = auth()->user();

        return $user?->company_id ?? null;
    }
}
