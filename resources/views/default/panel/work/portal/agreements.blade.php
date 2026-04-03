@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('My Agreements'))

@section('content')
<div class="container-xl">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ __('Service Agreements') }}</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('portal.service.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Start') }}</th>
                        <th>{{ __('End') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agreements as $agreement)
                        <tr>
                            <td>{{ $agreement->name ?? '#'.$agreement->id }}</td>
                            <td><span class="badge bg-green-lt">{{ ucfirst($agreement->status) }}</span></td>
                            <td>{{ $agreement->start_date?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $agreement->end_date?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">{{ __('No agreements found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
