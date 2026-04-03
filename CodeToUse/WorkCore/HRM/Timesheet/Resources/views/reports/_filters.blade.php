<form method="get" class="row g-2 mb-3">
    <div class="col-md-3">
        <label class="form-label">{{ __('Timesheet::timesheet.reports.from') }}</label>
        <input type="date" name="from" value="{{ optional($from)->toDateString() }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">{{ __('Timesheet::timesheet.reports.to') }}</label>
        <input type="date" name="to" value="{{ optional($to)->toDateString() }}" class="form-control">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary w-100">{{ __('Timesheet::timesheet.reports.apply') }}</button>
    </div>
</form>
