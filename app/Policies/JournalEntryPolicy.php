<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Money\JournalEntry;
use App\Models\User;

class JournalEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, JournalEntry $entry): bool
    {
        return $entry->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }
}
