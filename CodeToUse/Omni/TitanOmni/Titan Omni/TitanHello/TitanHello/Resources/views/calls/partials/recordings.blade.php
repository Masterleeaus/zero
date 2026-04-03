@if($call->recordings && $call->recordings->count())
    <ul class="list-group">
        @foreach($call->recordings as $r)
            <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold">
                            {{ $r->provider_recording_sid ?? ('Recording #' . $r->id) }}
                            @if($r->kind === 'voicemail')
                                <span class="badge bg-dark ms-2">Voicemail</span>
                            @endif
                            @if($r->fetch_status === 'ok')
                                <span class="badge bg-success ms-2">Stored</span>
                            @elseif($r->fetch_status === 'failed')
                                <span class="badge bg-danger ms-2">Fetch failed</span>
                            @elseif($r->fetch_status === 'pruned')
                                <span class="badge bg-secondary ms-2">Pruned</span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            {{ $r->duration_seconds ?? '' }}s
                            @if($r->bytes)
                                · {{ number_format($r->bytes/1024, 1) }} KB
                            @endif
                            @if($r->fetched_at)
                                · fetched {{ $r->fetched_at }}
                            @endif
                        </div>
                        @if($r->fetch_error)
                            <div class="text-danger small mt-1">{{ $r->fetch_error }}</div>
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        @if($r->recording_url)
                            <a class="btn btn-sm btn-outline-secondary" href="{{ $r->recording_url }}" target="_blank">Provider URL</a>
                        @endif
                        @if($r->stored_path)
                            <span class="badge bg-light text-dark">stored: {{ $r->stored_path }}</span>
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
@else
    <div class="text-muted">No recordings yet.</div>
@endif
