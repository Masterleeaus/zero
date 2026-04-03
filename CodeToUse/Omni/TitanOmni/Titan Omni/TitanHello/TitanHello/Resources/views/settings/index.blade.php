@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="mb-3">Titan Hello – Settings</h3>

    @if(session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-header"><strong>Twilio</strong></div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Pass 1 uses <code>.env</code> only. Later passes can add DB-backed settings.
            </p>

            <ul class="mb-3">
                <li><code>TITANHELLO_TWILIO_AUTH_TOKEN</code> set: <strong>{{ $twilio['auth_token_set'] ? 'Yes' : 'No' }}</strong></li>
                <li><code>TITANHELLO_TWILIO_REQUIRE_SIGNATURE</code>: <strong>{{ $twilio['require_signature'] ? 'true' : 'false' }}</strong></li>
            </ul>

            <p class="mb-0">Webhook endpoints:</p>
            <ul class="mb-0">
                <li><code>/titanhello/webhooks/voice/inbound</code></li>
                <li><code>/titanhello/webhooks/voice/status</code></li>
                <li><code>/titanhello/webhooks/voice/recording</code></li>
            </ul>
        </div>
    </div>
</div>
@endsection
