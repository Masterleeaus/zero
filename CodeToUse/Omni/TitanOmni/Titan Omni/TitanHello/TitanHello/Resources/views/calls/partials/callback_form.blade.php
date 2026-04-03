<form method="POST" action="{{ route('titanhello.calls.callback', $call->id) }}" class="d-flex gap-2 align-items-end">
    @csrf
    <div class="flex-grow-1">
        <label class="form-label">Callback due at</label>
        <input type="datetime-local" name="callback_due_at" class="form-control" value="{{ optional($call->callback_due_at)->format('Y-m-d\TH:i') }}">
    </div>
    <button class="btn btn-outline-primary" type="submit">Set callback</button>
</form>