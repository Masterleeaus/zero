@extends('chatbot::overlay.layout')
@section('content')
<div class="card">
    <h1>Titan Command Chatbots</h1>
    <p class="muted">Overlay pack merged into the chatbot core with channel ingestion, tenancy alignment, and operator handoff surfaces.</p>
</div>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Total Conversations</th>
            <th>Open</th>
            <th>Channels</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($chatbots as $chatbot)
            <tr>
                <td>{{ $chatbot->id }}</td>
                <td>{{ $chatbot->title }}</td>
                <td>{{ $chatbot->conversations_count }}</td>
                <td>{{ $chatbot->open_conversations_count }}</td>
                <td>{{ $chatbot->channels()->count() }}</td>
                <td><a href="{{ route('dashboard.chatbot.overlay.command.show', $chatbot) }}">Open</a></td>
            </tr>
        @empty
            <tr><td colspan="6">No chatbots found.</td></tr>
        @endforelse
    </tbody>
</table>
<div style="margin-top:16px">{{ $chatbots->links() }}</div>
@endsection
