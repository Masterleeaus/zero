@extends('layouts.main')

@section('page-title')
    {{ __('Timesheet::timesheet.approvals.inbox') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('Timesheet::timesheet.approvals.inbox') }}</h5>
                <a href="{{ route('timesheet.approvals.my_week') }}" class="btn btn-sm btn-light">{{ __('Timesheet::timesheet.actions.back') }}</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Timesheet::timesheet.fields.user') }}</th>
                                <th>{{ __('Timesheet::timesheet.approvals.week') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.status') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.submitted_at') }}</th>
                                <th class="text-end">{{ __('Timesheet::timesheet.actions.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pending as $s)
                                <tr>
                                    <td>{{ $s->id }}</td>
                                    <td>{{ $s->user_id }}</td>
                                    <td>{{ $s->week_start }} → {{ $s->week_end }}</td>
                                    <td>{{ $s->status }}</td>
                                    <td>{{ $s->submitted_at }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('timesheet.approvals.show', $s->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye"></i> {{ __('Timesheet::timesheet.actions.view') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">{{ __('Timesheet::timesheet.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $pending->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
