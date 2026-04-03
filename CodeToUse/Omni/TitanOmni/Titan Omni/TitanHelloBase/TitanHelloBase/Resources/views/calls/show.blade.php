@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="mb-1">Call #{{ $call->id }}</h3>
            <div>@include('titanhello::calls.partials.badge', ['call' => $call])</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('titanhello.calls.index') }}">Back to inbox</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    
@if($call->voicemail_flag)
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Voicemail</strong>
            <span class="text-muted small">Received: {{ $call->voicemail_received_at }}</span>
        </div>
        <div class="card-body">
            @if($call->voicemail_recording_id)
                <div class="mb-2">
                    <span class="text-muted">Recording ID:</span> #{{ $call->voicemail_recording_id }}
                </div>
            @endif

            @if($call->voicemail_summary_artifact_id || $call->voicemail_transcript_artifact_id)
                <div class="mb-2">
                    <span class="text-muted">Titan Zero artifacts:</span>
                    @if($call->voicemail_transcript_artifact_id)
                        <span class="badge bg-secondary">Transcript {{ $call->voicemail_transcript_artifact_id }}</span>
                    @endif
                    @if($call->voicemail_summary_artifact_id)
                        <span class="badge bg-secondary">Summary {{ $call->voicemail_summary_artifact_id }}</span>
                    @endif
                </div>
                <div class="small text-muted">Artifacts are stored and rendered by Titan Zero (phone-only module remains non-AI).</div>
            @else
                <div class="small text-muted">If enabled, Titan Zero can generate transcript/summary artifacts after the recording is fetched.</div>
            @endif
        </div>
    </div>
@endif

<div class="row g-3">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">Call details</h5>
                    <div class="mb-1"><strong>From:</strong> {{ $call->from_number }}</div>
                    <div class="mb-1"><strong>To:</strong> {{ $call->to_number }}</div>
                    <div class="mb-1"><strong>Provider:</strong> {{ $call->provider }}</div>
                    <div class="mb-1"><strong>SID:</strong> {{ $call->provider_call_sid }}</div>
                    <div class="mb-1"><strong>Created:</strong> {{ $call->created_at }}</div>
                    <div class="mb-1"><strong>Last event:</strong> {{ $call->last_event_at ?? $call->updated_at }}</div>
                    @if($call->callback_due_at)
                        <div class="mt-2"><strong>Callback due:</strong> {{ $call->callback_due_at }}</div>
                    @endif
                </div>
            </div>

            <div class="card mb-3"><div class="card-body">
                <h5 class="card-title mb-3">Assignment</h5>
                @include('titanhello::calls.partials.assign_form')
            </div></div>

            <div class="card mb-3"><div class="card-body">
                <h5 class="card-title mb-3">Disposition</h5>
                @include('titanhello::calls.partials.disposition_form')
            </div></div>

            <div class="card"><div class="card-body">
                <h5 class="card-title mb-3">Callback</h5>
                @include('titanhello::calls.partials.callback_form')
            </div></div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">Recordings</h5>
                    @include('titanhello::calls.partials.recordings')
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">Timeline</h5>
                    @include('titanhello::calls.partials.timeline')
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">Notes</h5>
                    <div class="mb-3">@include('titanhello::calls.partials.note_form')</div>
                    @include('titanhello::calls.partials.notes_list')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
