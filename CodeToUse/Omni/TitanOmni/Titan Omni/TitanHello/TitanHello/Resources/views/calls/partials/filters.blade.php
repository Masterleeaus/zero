<form method="GET" class="row g-2 align-items-end" data-titanhello-filters>
    <div class="col-md-2">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" data-autosubmit>
            <option value="">All</option>
            @foreach(['ringing','in-progress','completed','busy','failed','no-answer','recorded','queued'] as $st)
                <option value="{{ $st }}" @selected(($filters['status'] ?? '')===$st)>{{ $st }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Direction</label>
        <select name="direction" class="form-select" data-autosubmit>
            <option value="">All</option>
            <option value="inbound" @selected(($filters['direction'] ?? '')==='inbound')>inbound</option>
            <option value="outbound" @selected(($filters['direction'] ?? '')==='outbound')>outbound</option>
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">Assigned</label>
        <select name="assigned" class="form-select" data-autosubmit>
            <option value="">All</option>
            <option value="unassigned" @selected(($filters['assigned'] ?? '')==='unassigned')>Unassigned</option>
            <option value="me" @selected(($filters['assigned'] ?? '')==='me')>Me</option>
        </select>
    </div>

    <div class="col-md-2">
        <label class="form-label">From</label>
        <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">To</label>
        <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">Search</label>
        <input type="text" name="q" class="form-control" placeholder="number / sid" value="{{ $filters['q'] ?? '' }}">
    </div>

    <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary" type="submit">Apply</button>
        <a class="btn btn-outline-secondary" href="{{ route('titanhello.calls.index') }}" data-titanhello-clear>Clear</a>
    </div>
</form>