@extends('chatbot::overlay.layout')
@section('content')
<div class="card"><h1>Conversation #{{ $conversation->id }}</h1><p>{{ $conversation->chatbot?->title }}</p></div>
<div class="messages">@foreach($messages as $message)<div class="msg"><strong>{{ $message->role }}</strong><div>{{ $message->message }}</div></div>@endforeach</div><div style="margin-top:16px">{{ $messages->links() }}</div>
<form method="post" action="{{ route('dashboard.chatbot.overlay.customer.message', $conversation) }}" enctype="multipart/form-data" class="card">@csrf<textarea name="message" rows="4"></textarea><br><input type="file" name="attachment"><br><button type="submit">Send message</button></form>
<form method="post" action="{{ route('dashboard.chatbot.overlay.customer.feedback', $conversation) }}" class="card">@csrf<label>Rating <input type="number" name="rating" min="1" max="5"></label><br><textarea name="feedback" rows="3"></textarea><br><button type="submit">Save feedback</button></form>
@endsection