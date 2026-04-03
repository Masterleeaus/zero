@extends('layouts.main')

@section('page-title')
    {{ __('Timesheet::timesheet.menu.settings') }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Timesheet::timesheet.menu.settings') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('timesheet.settings.update') }}">
                    @csrf
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="costing_enabled" name="costing_enabled" value="1" @checked($data['costing_enabled'])>
                        <label class="form-check-label" for="costing_enabled">{{ __('Timesheet::timesheet.settings.costing_enabled') }}</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="timer_enabled" name="timer_enabled" value="1" @checked($data['timer_enabled'])>
                        <label class="form-check-label" for="timer_enabled">{{ __('Timesheet::timesheet.settings.timer_enabled') }}</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="approvals_enabled" name="approvals_enabled" value="1" @checked($data['approvals_enabled'])>
                        <label class="form-check-label" for="approvals_enabled">{{ __('Timesheet::timesheet.settings.approvals_enabled') }}</label>
                    </div>

                    <button class="btn btn-primary">{{ __('Timesheet::timesheet.actions.save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
