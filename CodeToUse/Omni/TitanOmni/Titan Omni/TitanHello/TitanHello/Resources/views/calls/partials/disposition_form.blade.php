<form method="POST" action="{{ route('titanhello.calls.disposition', $call->id) }}">
    @csrf
    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label">Disposition</label>
            <input type="text" name="disposition" class="form-control" value="{{ $call->disposition }}" placeholder="booked / quoted / spam">
        </div>
        <div class="col-md-2">
            <label class="form-label">Priority</label>
            <input type="number" name="priority" class="form-control" min="0" max="10" value="{{ $call->priority ?? 0 }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Notes</label>
            <input type="text" name="disposition_notes" class="form-control" value="{{ $call->disposition_notes }}" placeholder="quick notes...">
        </div>
        <div class="col-12">
            <button class="btn btn-outline-primary" type="submit">Save disposition</button>
        </div>
    </div>
</form>