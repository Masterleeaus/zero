@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('My Assets'))

@section('content')
<div class="container-xl">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ __('Installed Equipment') }}</h2>
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
                        <th>{{ __('Equipment') }}</th>
                        <th>{{ __('Serial') }}</th>
                        <th>{{ __('Installed') }}</th>
                        <th>{{ __('Warranty Expiry') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr>
                            <td>{{ $asset->name ?? $asset->equipment?->name ?? '#'.$asset->id }}</td>
                            <td>{{ $asset->serial_number ?? '—' }}</td>
                            <td>{{ $asset->installed_at?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $asset->warranty_expiry?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">{{ __('No assets found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
