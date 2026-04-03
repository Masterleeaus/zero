@extends('layouts.main')

@section('page-title')
    {{ __('Timesheet::timesheet.reports.title') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('Timesheet::timesheet.reports.title') }}</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('timesheet.index') }}" class="btn btn-sm btn-light">{{ __('Timesheet::timesheet.actions.back') }}</a>
                </div>
            </div>
            <div class="card-body">
                @include('timesheet::reports._filters')

                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted">{{ __('Timesheet::timesheet.reports.entries') }}</div>
                            <div class="h3 mb-0">{{ $summary['entries'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted">{{ __('Timesheet::timesheet.reports.total_hours') }}</div>
                            <div class="h3 mb-0">{{ $summary['hours_decimal'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted">{{ __('Timesheet::timesheet.reports.total_cost') }}</div>
                            <div class="h3 mb-0">{{ number_format($summary['cost_total'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-muted">{{ __('Timesheet::timesheet.reports.range') }}</div>
                            <div class="h6 mb-0">{{ optional($from)->toDateString() }} → {{ optional($to)->toDateString() }}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('timesheet.reports.crew', ['from'=>$from->toDateString(),'to'=>$to->toDateString()]) }}" class="btn btn-sm btn-outline-primary">{{ __('Timesheet::timesheet.reports.by_crew') }}</a>
                </div>

                <hr>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <h6 class="mb-2 d-flex justify-content-between">
                            <span>{{ __('Timesheet::timesheet.reports.by_project') }}</span>
                            <a href="{{ route('timesheet.reports.projects', ['from'=>$from->toDateString(),'to'=>$to->toDateString()]) }}" class="small">{{ __('Timesheet::timesheet.reports.view_all') }}</a>
                        </h6>
                        @include('timesheet::reports.partials.table_breakdown', ['rows' => array_slice($byProject,0,8), 'type' => 'project'])
                    </div>
                    <div class="col-lg-6">
                        <h6 class="mb-2 d-flex justify-content-between">
                            <span>{{ __('Timesheet::timesheet.reports.by_work_order') }}</span>
                            <a href="{{ route('timesheet.reports.work_orders', ['from'=>$from->toDateString(),'to'=>$to->toDateString()]) }}" class="small">{{ __('Timesheet::timesheet.reports.view_all') }}</a>
                        </h6>
                        @include('timesheet::reports.partials.table_breakdown', ['rows' => array_slice($byWorkOrder,0,8), 'type' => 'work_order'])
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
