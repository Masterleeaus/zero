<form method="GET" action="{{ route('timesheet.index') }}" class="row g-2 mb-3">
    <div class="col-md-3">
        <input type="date" name="from" class="form-control" value="{{ request('from') }}">
    </div>
    <div class="col-md-3">
        <input type="date" name="to" class="form-control" value="{{ request('to') }}">
    </div>
    <div class="col-md-3">
        <button class="btn btn-primary" type="submit">{{ __('Filter') }}</button>
        <a class="btn btn-outline-secondary" href="{{ route('timesheet.index') }}">{{ __('Reset') }}</a>
    </div>
</form>
