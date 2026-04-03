@if($call->notes && $call->notes->count())
    <ul class="list-group">
        @foreach($call->notes as $n)
            <li class="list-group-item">
                <div class="small text-muted">{{ $n->created_at }} @if($n->user_id) · user #{{ $n->user_id }} @endif</div>
                <div>{{ $n->note }}</div>
            </li>
        @endforeach
    </ul>
@else
    <div class="text-muted">No notes yet.</div>
@endif