@extends('chatbot::overlay.layout')
@section('content')
<div class="card">
    <h1>{{ $chatbot->title }}</h1>
    <p class="muted">Channels: {{ $chatbot->channels->count() }} | Conversations: {{ $chatbot->conversations->count() }} | Workspace: {{ $chatbot->workspace_id ?? $chatbot->company_id ?? $chatbot->team_id ?? 'n/a' }}</p>
</div>

@include('chatbot::overlay.command.partials.channel-cards', ['chatbot' => $chatbot])

<div class="card" style="margin-top:16px">
    <strong>Recent conversations</strong>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Channel</th>
                <th>Customer</th>
                <th>Assigned Agent</th>
                <th>Status</th>
                <th>Last Activity</th>
            </tr>
        </thead>
        <tbody>
            @forelse($conversations as $conversation)
                <tr>
                    <td>{{ $conversation->id }}</td>
                    <td>{{ ucfirst($conversation->chatbot_channel ?? optional($conversation->chatbotChannel)->channel ?? 'web') }}</td>
                    <td>{{ $conversation->customer?->name ?? $conversation->conversation_name ?? '—' }}</td>
                    <td>{{ $conversation->assignedAgent?->name ?? 'Unassigned' }}</td>
                    <td>{{ $conversation->closed ? 'Closed' : 'Open' }}</td>
                    <td>{{ optional($conversation->last_activity_at)?->diffForHumans() ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No conversations yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:16px">{{ $conversations->links() }}</div>
</div>
@endsection
