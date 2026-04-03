@if($call->events && $call->events->count())
    <ul class="list-group">
        @foreach($call->events as $e)
            <li class="list-group-item d-flex justify-content-between">
                <div>
                    <div class="fw-semibold">{{ $e->event_type }}</div>
                    <div class="text-muted small">{{ $e->status }}</div>
                </div>
                <div class="text-muted small">{{ $e->occurred_at ?? $e->created_at }}</div>
            </li>
        @endforeach
    </ul>
@else
    <div class="text-muted">No events yet.</div>
@endif