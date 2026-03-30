<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\TicketAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSupport;
use App\Notifications\LiveNotification;
use App\Services\Support\SupportLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    public function __construct(private SupportLifecycleService $lifecycle) {}

    public function list()
    {
        $user = auth()->user();
        $status = request('status');

        $this->evaluateLifecycle($user?->company_id);

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

        $admins = User::where('company_id', $support->company_id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'support']))
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new LiveNotification(
                message: "New support ticket: {$support->subject}",
                link: route('dashboard.support.view', $support),
                title: 'New Support Ticket'
            ));
        }

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

        $this->lifecycle->processReplies($ticket, $user->isAdmin() ? 'agent' : 'user');
    }

    public function resolve(UserSupport $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $this->lifecycle->resolve($ticket);

        return back()->with('message', __('Ticket resolved'));
    }

    protected function evaluateLifecycle(?int $companyId): void
    {
        if (! $companyId) {
            return;
        }

        $this->lifecycle->markStale($companyId, now()->subDays(7));
        $this->lifecycle->autoResolveInactive($companyId, now()->subDays(30));
    }
}
