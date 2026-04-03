@extends('layouts.main')

@section('page-title')
    {{ __('Timesheet::timesheet.reports.by_work_order') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('Timesheet::timesheet.reports.by_work_order') }}</h5>
                <a href="{{ route('timesheet.reports.dashboard', ['from'=>$from->toDateString(),'to'=>$to->toDateString()]) }}" class="btn btn-sm btn-light">{{ __('Timesheet::timesheet.actions.back') }}</a>
            </div>
            <div class="card-body">
                @include('timesheet::reports._filters')

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Timesheet::timesheet.reports.item') }}</th>
                                <th class="text-end">{{ __('Timesheet::timesheet.reports.entries') }}</th>
                                <th class="text-end">{{ __('Timesheet::timesheet.reports.total_hours') }}</th>
                                <th class="text-end">{{ __('Timesheet::timesheet.reports.total_cost') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($rows as $r)
                            <tr>
                                <td>{{ $r['label'] }}</td>
                                <td class="text-end">{{ $r['entries'] }}</td>
                                <td class="text-end">{{ $r['hours_decimal'] }}</td>
                                <td class="text-end">{{ number_format($r['cost_total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">{{ __('Timesheet::timesheet.reports.none') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
