@extends('layouts.app')

@section('pageTitle', __('Workflow Timeline'))

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">{{ __('Workflow Timeline') }}: {{ $workflow->name }}</h1>
        <a class="btn btn-light" href="{{ route('workflow.account.workflows.index') }}">{{ __('Back') }}</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>{{ __('Time') }}</th>
                        <th>{{ __('Level') }}</th>
                        <th>{{ __('Message') }}</th>
                        <th>{{ __('Context') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at }}</td>
                            <td><code>{{ $log->level }}</code></td>
                            <td>{{ $log->message }}</td>
                            <td><pre class="mb-0" style="white-space:pre-wrap">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">{{ __('No logs yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
