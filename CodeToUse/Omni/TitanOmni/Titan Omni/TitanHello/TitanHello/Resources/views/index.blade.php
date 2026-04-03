@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-2">Titan Hello</h3>
                    <p class="text-muted mb-3">Phone-only calling and Call Inbox for Worksuite.</p>

                    <div class="d-flex gap-2 flex-wrap">
                        <a class="btn btn-primary" href="{{ route('titanhello.calls.index') }}">
                            Open Call Inbox
                        </a>
                        <a class="btn btn-outline-secondary" href="{{ route('titanhello.settings.index') }}">
                            Settings
                        </a>
                        <a class="btn btn-outline-secondary" href="{{ route('titanhello.health') }}">
                            Health
                        </a>
                    </div>

                    <hr class="my-4">

                    <h5>Provider webhooks</h5>
                    <ul class="mb-0">
                        <li><code>POST /titanhello/webhooks/voice/inbound</code></li>
                        <li><code>POST /titanhello/webhooks/voice/status</code></li>
                        <li><code>POST /titanhello/webhooks/voice/recording</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
