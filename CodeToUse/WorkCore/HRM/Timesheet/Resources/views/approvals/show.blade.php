@extends('layouts.main')

@section('page-title')
    {{ __('Timesheet::timesheet.approvals.review') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('Timesheet::timesheet.approvals.review') }} #{{ $submission->id }}</h5>
                <a href="{{ route('timesheet.approvals.inbox') }}" class="btn btn-sm btn-light">{{ __('Timesheet::timesheet.actions.back') }}</a>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div><strong>{{ __('Timesheet::timesheet.fields.user') }}:</strong> {{ $submission->user_id }}</div>
                    <div><strong>{{ __('Timesheet::timesheet.approvals.week') }}:</strong> {{ $submission->week_start }} → {{ $submission->week_end }}</div>
                    <div><strong>{{ __('Timesheet::timesheet.fields.status') }}:</strong> {{ $submission->status }}</div>
                    @if($submission->submitter_notes)
                        <div class="mt-2"><strong>{{ __('Timesheet::timesheet.approvals.submitter_notes') }}:</strong><br>{{ $submission->submitter_notes }}</div>
                    @endif
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle">
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
                            @foreach($submission->items as $item)
                                @php $e = $item->timesheet; @endphp
                                <tr>
                                    <td>{{ optional($e->date)->format('Y-m-d') }}</td>
                                    <td>{{ $e->project_id ?? '—' }}</td>
                                    <td>{{ $e->task_id ?? '—' }}</td>
                                    <td class="text-end">{{ (int)$e->hours }}h {{ (int)$e->minutes }}m</td>
                                    <td class="text-end">{{ $e->fsm_cost_total !== null ? number_format((float)$e->fsm_cost_total, 2) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($submission->status === 'submitted')
                    <div class="row g-2">
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('timesheet.approvals.approve', $submission->id) }}">
                                @csrf
                                <label class="form-label">{{ __('Timesheet::timesheet.approvals.approver_notes') }}</label>
                                <textarea name="approver_notes" class="form-control" rows="2"></textarea>
                                <button class="btn btn-success mt-2"><i class="ti ti-check"></i> {{ __('Timesheet::timesheet.approvals.approve') }}</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('timesheet.approvals.reject', $submission->id) }}">
                                @csrf
                                <label class="form-label">{{ __('Timesheet::timesheet.approvals.approver_notes') }}</label>
                                <textarea name="approver_notes" class="form-control" rows="2"></textarea>
                                <button class="btn btn-danger mt-2"><i class="ti ti-x"></i> {{ __('Timesheet::timesheet.approvals.reject') }}</button>
                            </form>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
