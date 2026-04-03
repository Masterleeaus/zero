<tr>
    <td>#{{ $c->id }}</td>
    <td>@include('titanhello::calls.partials.badge', ['call' => $c])</td>
    <td>{{ $c->from_number }}</td>
    <td>{{ $c->to_number }}</td>
    <td>
        @if($c->assigned_to_user_id)
            <span class="text-muted">Assigned</span>
        @else
            <span class="text-muted">Unassigned</span>
        @endif
    </td>
    <td>{{ $c->last_event_at ?? $c->updated_at }}</td>
    <td class="text-end">
        <a class="btn btn-sm btn-primary" href="{{ route('titanhello.calls.show', $c->id) }}">Open</a>
    </td>
</tr>