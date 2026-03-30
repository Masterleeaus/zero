<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\TicketAction;
use App\Http\Controllers\Controller;
use App\Models\UserSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    public function list()
    {
        $user = auth()->user();

        $items = UserSupport::query()
            ->when(! $user?->isAdmin(), fn ($q) => $q->where('user_id', $user?->id))
            ->latest()
            ->get();

        return view('default.panel.support.list', compact('items'));
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
            'status'     => 'Submitted a Ticket',
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
    }
}
