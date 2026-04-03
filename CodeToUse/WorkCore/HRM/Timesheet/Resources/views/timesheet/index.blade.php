@extends('layouts.main')

@section('page-title')
    {{ __('Timesheet::timesheet.menu.title') }}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('Timesheet::timesheet.menu.entries') }}</h5>
                <div class="d-flex gap-2">
                    @permission('timesheet create')
                        <a href="{{ route('timesheet.create') }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-plus"></i> {{ __('Timesheet::timesheet.actions.add') }}
                        </a>
                    @endpermission
                    @permission('timesheet manage')
                        <a href="{{ route('timesheet.export.csv') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-download"></i> {{ __('Timesheet::timesheet.actions.export_csv') }}
                        </a>
                    @endpermission
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Timesheet::timesheet.filters.from') }}</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Timesheet::timesheet.filters.to') }}</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>
                    <div class="col-md-3 align-self-end">
                        <button class="btn btn-sm btn-secondary"><i class="ti ti-filter"></i> {{ __('Timesheet::timesheet.actions.filter') }}</button>
                        <a href="{{ route('timesheet.index') }}" class="btn btn-sm btn-light">{{ __('Timesheet::timesheet.actions.reset') }}</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Timesheet::timesheet.fields.date') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.user') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.project') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.task') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.work_order') }}</th>
                                <th class="text-end">{{ __('Timesheet::timesheet.fields.time') }}</th>
                                <th>{{ __('Timesheet::timesheet.fields.type') }}</th>
                                <th class="text-end">{{ __('Timesheet::timesheet.fields.cost') }}</th>
                                <th class="text-end">{{ __('Timesheet::timesheet.actions.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($timesheets as $t)
                                <tr>
                                    <td>{{ optional($t->date)->format('Y-m-d') }}</td>
                                    <td>{{ $t->user_id }}</td>
                                    <td>{{ $t->project_id ?? '—' }}</td>
                                    <td>{{ $t->task_id ?? '—' }}</td>
                                    <td>{{ $t->work_order_id ?? '—' }}</td>
                                    <td class="text-end">{{ (int)$t->hours }}h {{ (int)$t->minutes }}m</td>
                                    <td>{{ $t->type ?? 'regular' }}</td>
                                    <td class="text-end">{{ $t->fsm_cost_total !== null ? number_format((float)$t->fsm_cost_total, 2) : '—' }}</td>
                                    <td class="text-end">
                                        @permission('timesheet edit')
                                            <a href="{{ route('timesheet.edit', $t->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                        @endpermission
                                        @permission('timesheet delete')
                                            <form method="POST" action="{{ route('timesheet.destroy', $t->id) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('Timesheet::timesheet.confirm.delete') }}')">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        @endpermission
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        {{ __('Timesheet::timesheet.empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $timesheets->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
