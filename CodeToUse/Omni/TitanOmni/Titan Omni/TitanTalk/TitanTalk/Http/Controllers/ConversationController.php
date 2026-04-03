<?php
namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanTalk\Models\Conversation;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $query = Conversation::query()->orderBy('id', 'desc');

        if ($search = $request->get('q')) {
            $query->where('external_ref', 'like', '%' . $search . '%');
        }

        if ($channel = $request->get('channel')) {
            $query->where('channel', $channel);
        }

        if ($clientId = $request->get('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($projectId = $request->get('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($leadId = $request->get('lead_id')) {
            $query->where('lead_id', $leadId);
        }

        if ($ticketId = $request->get('ticket_id')) {
            $query->where('ticket_id', $ticketId);
        }

        if ($taskId = $request->get('task_id')) {
            $query->where('task_id', $taskId);
        }

        if ($invoiceId = $request->get('invoice_id')) {
            $query->where('invoice_id', $invoiceId);
        }

        $conversations = $query->paginate(25);

        return view('titantalk::conversation.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $conversation->load('messages');

        return view('titantalk::conversation.show', compact('conversation'));
    }

    public function updateCrm(Request $request, Conversation $conversation)
    {
        $data = $request->validate([
            'client_id'  => 'nullable|integer',
            'lead_id'    => 'nullable|integer',
            'project_id' => 'nullable|integer',
            'ticket_id'  => 'nullable|integer',
            'task_id'    => 'nullable|integer',
            'invoice_id' => 'nullable|integer',
        ]);

        $conversation->fill($data);
        $conversation->save();

        return redirect()
            ->route('titantalk.conversations.index', $request->only('q','channel','client_id','project_id','lead_id','ticket_id','task_id','invoice_id'))
            ->with('success', 'Conversation links updated.');
    }
}
