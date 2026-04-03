@extends('layouts.app')

@section('pageTitle', __('Workflows'))

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">{{ __('Workflows') }}</h1>
        @can('manage_workflow')
            <a href="{{ route('workflow.account.workflows.create') }}" class="btn btn-primary">
                <i class="fa fa-plus-circle"></i> {{ __('Add Workflow') }}
            </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Trigger') }}</th>
                        <th>{{ __('Active') }}</th>
                        <th style="width:220px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workflows as $wf)
                        <tr>
                            <td>{{ $wf->id }}</td>
                            <td>{{ $wf->name }}</td>
                            <td><code>{{ $wf->trigger_event ?? '—' }}</code></td>
                            <td>{!! $wf->is_active ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' !!}</td>
                            <td>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('workflow.account.workflows.timeline', $wf->id) }}">{{ __('Timeline') }}</a>
                                @can('manage_workflow')
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('workflow.account.workflows.edit', $wf->id) }}">{{ __('Edit') }}</a>
                                    <form action="{{ route('workflow.account.workflows.run', $wf->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success" type="submit">{{ __('Run') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">{{ __('No workflows found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $workflows->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
