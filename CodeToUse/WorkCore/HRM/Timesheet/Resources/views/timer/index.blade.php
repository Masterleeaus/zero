@extends('layouts.main')

@section('page-title')
    {{ __('Timesheet::timesheet.menu.timer') }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Timesheet::timesheet.timer.current') }}</h5>
            </div>
            <div class="card-body">
                @if($running)
                    <div class="alert alert-success">
                        <div><strong>{{ __('Timesheet::timesheet.timer.running') }}</strong></div>
                        <div class="text-muted">{{ __('Timesheet::timesheet.fields.started_at') }}: {{ $running->started_at }}</div>
                        <div class="text-muted">{{ __('Timesheet::timesheet.fields.project') }}: {{ $running->project_id ?? '—' }}</div>
                        <div class="text-muted">{{ __('Timesheet::timesheet.fields.task') }}: {{ $running->task_id ?? '—' }}</div>
                    </div>
                    <form method="POST" action="{{ route('timesheet.timer.stop') }}" class="d-flex gap-2">
                        @csrf
                        <input type="hidden" name="convert" value="1">
                        <select name="type" class="form-select">
                            <option value="regular">{{ __('Timesheet::timesheet.types.regular') }}</option>
                            <option value="overtime">{{ __('Timesheet::timesheet.types.overtime') }}</option>
                        </select>
                        <button class="btn btn-danger"><i class="ti ti-player-stop"></i> {{ __('Timesheet::timesheet.timer.stop') }}</button>
                    </form>
                @else
                    <div class="alert alert-secondary">{{ __('Timesheet::timesheet.timer.no_running') }}</div>
                    <form method="POST" action="{{ route('timesheet.timer.start') }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">{{ __('Timesheet::timesheet.fields.project') }}</label>
                            <input type="number" name="project_id" class="form-control" placeholder="ID">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Timesheet::timesheet.fields.task') }}</label>
                            <input type="number" name="task_id" class="form-control" placeholder="ID">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Timesheet::timesheet.fields.work_order') }}</label>
                            <input type="number" name="work_order_id" class="form-control" placeholder="ID">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Timesheet::timesheet.fields.notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <button class="btn btn-primary"><i class="ti ti-player-play"></i> {{ __('Timesheet::timesheet.timer.start') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Timesheet::timesheet.timer.recent') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Timesheet::timesheet.fields.started_at') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.stopped_at') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.status') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.project') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.task') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent as $t)
                                <tr>
                                    <td>{{ $t->id }}</td>
                                    <td>{{ $t->started_at }}</td>
                                    <td>{{ $t->stopped_at ?? '—' }}</td>
                                    <td>{{ $t->status }}</td>
                                    <td>{{ $t->project_id ?? '—' }}</td>
                                    <td>{{ $t->task_id ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">{{ __('Timesheet::timesheet.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
