<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\TicketAction;
use App\Http\Controllers\Controller;
use App\Models\UserSupport;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    public function list()
    {
        $user = auth()->user();
        $status = request('status');

        $items = UserSupport::query()
            ->when(! $user?->isAdmin(), fn ($q) => $q->where('user_id', $user?->id))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15);

        return view('default.panel.support.list', compact('items', 'status'));
    }

    public function newTicket()
    {
        return view('default.panel.support.new');
    }

    public function newTicketSend(Request $request): void
    {
        if (! $user = Auth::user()) {
            return;
        }

        $support = $user->supportRequests()->create([
            'ticket_id'  => Str::upper(Str::random(10)),
            'priority'   => $request->priority,
            'category'   => $request->category,
            'subject'    => $request->subject,
            'company_id' => $user->company_id,
            'status'     => 'open',
        ]);

        TicketAction::ticket($support)
            ->fromUser()
            ->new($request->message)
            ->send();
    }

    public function viewTicket(UserSupport $ticket)
    {
        $this->authorize('view', $ticket);

        return view('default.panel.support.view', compact('ticket'));
    }

    public function viewTicketSendMessage(Request $request, UserSupport $ticket): void
    {
        $this->authorize('update', $ticket);

        if (! $user = Auth::user()) {
            return;
        }

        TicketAction::ticket($ticket)
            ->fromAdminIfTrue($user->isAdmin())
            ->answer($request->input('message'))
            ->send();

        $ticket->update([
            'status' => $user->isAdmin() ? 'waiting_on_user' : 'waiting_on_team',
        ]);
    }

    public function resolve(UserSupport $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $ticket->update(['status' => 'resolved']);

        return back()->with('message', __('Ticket resolved'));
    }
}
