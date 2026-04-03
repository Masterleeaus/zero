<form method="POST" action="{{ route('titanhello.calls.assign', $call->id) }}" class="d-flex gap-2 align-items-end">
    @csrf
    <div class="flex-grow-1">
        <label class="form-label">Assign to user ID</label>
        <input type="number" name="assigned_to_user_id" class="form-control" value="{{ $call->assigned_to_user_id }}">
    </div>
    <button class="btn btn-outline-primary" type="submit">Assign</button>
</form>