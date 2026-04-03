@extends('layouts.main')

@section('page-title')
    {{ __('Timesheet::timesheet.menu.approvals') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('Timesheet::timesheet.approvals.my_week') }} ({{ $weekStart }} → {{ $weekEnd }})</h5>
                <div class="d-flex gap-2">
                    @permission('timesheet approve')
                        <a href="{{ route('timesheet.approvals.inbox') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-inbox"></i> {{ __('Timesheet::timesheet.approvals.inbox') }}
                        </a>
                    @endpermission
                </div>
            </div>
            <div class="card-body">
                @if($submission && $submission->status === 'submitted')
                    <div class="alert alert-info">{{ __('Timesheet::timesheet.approvals.already_submitted') }}</div>
                @elseif($submission && $submission->status === 'approved')
                    <div class="alert alert-success">{{ __('Timesheet::timesheet.approvals.already_approved') }}</div>
                @elseif($submission && $submission->status === 'rejected')
                    <div class="alert alert-warning">{{ __('Timesheet::timesheet.approvals.was_rejected') }}</div>
                @endif

                <div class="table-responsive mb-3">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Timesheet::timesheet.fields.date') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.project') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.task') }}</th>
                                <th class="text-end">{{ __('Timesheet::timesheet.fields.time') }}</th>
                                <th class="text-end">{{ __('Timesheet::timesheet.fields.cost') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $e)
                                <tr>
                                    <td>{{ optional($e->date)->format('Y-m-d') }}</td>
                                    <td>{{ $e->project_id ?? '—' }}</td>
                                    <td>{{ $e->task_id ?? '—' }}</td>
                                    <td class="text-end">{{ (int)$e->hours }}h {{ (int)$e->minutes }}m</td>
                                    <td class="text-end">{{ $e->fsm_cost_total !== null ? number_format((float)$e->fsm_cost_total, 2) : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">{{ __('Timesheet::timesheet.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @permission('timesheet submit')
                    <form method="POST" action="{{ route('timesheet.approvals.submit') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $weekStart }}">
                        <div class="mb-2">
                            <label class="form-label">{{ __('Timesheet::timesheet.approvals.submitter_notes') }}</label>
                            <textarea name="submitter_notes" class="form-control" rows="2"></textarea>
                        </div>
                        <button class="btn btn-primary" @if($submission && in_array($submission->status, ['submitted','approved'])) disabled @endif>
                            <i class="ti ti-send"></i> {{ __('Timesheet::timesheet.approvals.submit') }}
                        </button>
                    </form>
                @endpermission
            </div>
        </div>
    </div>
</div>
@endsection
