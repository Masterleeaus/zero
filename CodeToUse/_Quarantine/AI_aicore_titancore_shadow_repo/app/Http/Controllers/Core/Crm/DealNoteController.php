<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Crm;

use App\Http\Controllers\Core\CoreController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DealNoteController extends CoreController
{
    public function index(string $deal): View
    {
        return $this->placeholder(
            __('Deal notes'),
            __('Notes for deal :deal will appear here.', ['deal' => $deal])
        );
    }

    public function store(Request $request, string $deal): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Note added to deal :deal.', ['deal' => $deal]),
        ]);
    }

    public function update(Request $request, string $deal, string $note): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Deal note :note updated.', ['note' => $note]),
        ]);
    }

    public function destroy(string $deal, string $note): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Deal note :note removed.', ['note' => $note]),
        ]);
    }
}
