<?php

declare(strict_types=1);

namespace App\Support\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Admin helper functions.
 *
 * Loaded via AdminServiceProvider using a require_once approach.
 * Keeps globally available admin utilities in one place.
 */
class AdminHelpers
{
    /**
     * Determine whether the currently authenticated user can access the admin panel.
     */
    public static function isAdminUser(?User $user = null): bool
    {
        $user ??= Auth::user();

        if ($user === null) {
            return false;
        }

        return $user->hasRole('admin') || $user->hasRole('super_admin');
    }

    /**
     * Format a Spatie permission name into a human-readable label.
     *
     * Example: "admin_users_edit" → "Admin Users Edit"
     */
    public static function permissionLabel(string $permission): string
    {
        return ucwords(str_replace(['_', '.'], ' ', $permission));
    }

    /**
     * Return the admin panel base URL prefix.
     */
    public static function adminPrefix(): string
    {
        return '/dashboard/admin';
    }
}
