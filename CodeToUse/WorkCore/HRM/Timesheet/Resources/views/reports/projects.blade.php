@extends('layouts.main')

@section('page-title')
    {{ __('Timesheet::timesheet.reports.by_project') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('Timesheet::timesheet.reports.by_project') }}</h5>
                <a href="{{ route('timesheet.reports.dashboard', ['from'=>$from->toDateString(),'to'=>$to->toDateString()]) }}" class="btn btn-sm btn-light">{{ __('Timesheet::timesheet.actions.back') }}</a>
            </div>
            <div class="card-body">
                @include('timesheet::reports._filters')

                @include('timesheet::reports.partials.table_breakdown', ['rows' => $rows, 'type' => 'project'])
            </div>
        </div>
    </div>
</div>
@endsection
