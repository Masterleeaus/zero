<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CleanerProfileController extends CoreController
{
    public function show(string $user): View
    {
        return $this->placeholder(
            __('Cleaner profile'),
            __('Profile for user :user.', ['user' => $user])
        );
    }

    public function edit(string $user): View
    {
        return $this->placeholder(
            __('Edit cleaner profile'),
            __('Update profile for user :user.', ['user' => $user])
        );
    }

    public function update(Request $request, string $user): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Cleaner profile updated.'),
        ]);
    }
}

