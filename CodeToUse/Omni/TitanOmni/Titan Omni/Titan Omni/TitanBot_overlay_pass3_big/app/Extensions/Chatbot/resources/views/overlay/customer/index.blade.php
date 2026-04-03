@extends('chatbot::overlay.layout')
@section('content')
<div class="card"><h1>Customer Conversations</h1></div>
<table><thead><tr><th>ID</th><th>Chatbot</th><th>Assigned Agent</th><th>Closed</th><th>Open</th></tr></thead><tbody>@foreach($conversations as $conversation)<tr><td>{{ $conversation->id }}</td><td>{{ $conversation->chatbot?->title }}</td><td>{{ $conversation->assignedAgent?->name ?? 'Unassigned' }}</td><td>{{ $conversation->closed ? 'Yes' : 'No' }}</td><td><a href="{{ route('dashboard.chatbot.overlay.customer.show', $conversation) }}">Open</a></td></tr>@endforeach</tbody></table><div style="margin-top:16px">{{ $conversations->links() }}</div>
@endsection