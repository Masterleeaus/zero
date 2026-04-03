@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Audit Log'))

@section('content')
    <div class="page-wrapper">
        <div class="container-xl">
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">{{ __('Audit Log') }}</h2>
                        <p class="text-muted mt-1">{{ __('System activity recorded in tz_audit_log') }}</p>
                    </div>
                </div>
            </div>

            <div class="page-body">
                {{-- Filters --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <form
                            method="GET"
                            action="{{ route('titan.admin.audit.index') }}"
                        >
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Action') }}</label>
                                    <select
                                        name="action"
                                        class="form-select"
                                    >
                                        <option value="">{{ __('All Actions') }}</option>
                                        @foreach ($actions as $action)
                                            <option
                                                value="{{ $action }}"
                                                @selected(($filters['action'] ?? '') === $action)
                                            >
                                                {{ $action }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('From') }}</label>
                                    <input
                                        type="date"
                                        name="from"
                                        class="form-control"
                                        value="{{ $filters['from'] ?? '' }}"
                                    />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('To') }}</label>
                                    <input
                                        type="date"
                                        name="to"
                                        class="form-control"
                                        value="{{ $filters['to'] ?? '' }}"
                                    />
                                </div>
                                <div class="col-md-3 d-flex align-items-end gap-2">
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >
                                        {{ __('Filter') }}
                                    </button>
                                    <a
                                        href="{{ route('titan.admin.audit.index') }}"
                                        class="btn btn-outline-secondary"
                                    >
                                        {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Log Table --}}
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Time') }}</th>
                                    <th>{{ __('Action') }}</th>
                                    <th>{{ __('Process') }}</th>
                                    <th>{{ __('Performed By') }}</th>
                                    <th>{{ __('Details') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td class="text-muted text-nowrap">
                                            {{ $log->created_at?->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-blue-lt">{{ $log->action }}</span>
                                        </td>
                                        <td class="text-muted font-monospace small">
                                            {{ Str::limit($log->process_id, 40) }}
                                        </td>
                                        <td>
                                            {{ $log->performer?->name ?? '—' }}
                                        </td>
                                        <td class="text-muted small">
                                            @if ($log->details)
                                                <code>{{ Str::limit(json_encode($log->details), 120) }}</code>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="5"
                                            class="text-center text-muted py-4"
                                        >
                                            {{ __('No audit entries found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($logs->hasPages())
                        <div class="card-footer d-flex align-items-center">
                            {{ $logs->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
