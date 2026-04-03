@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('My Quotes'))

@section('content')
<div class="container-xl">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ __('Quotes') }}</h2>
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
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotes as $quote)
                        <tr>
                            <td>{{ $quote->reference ?? '#'.$quote->id }}</td>
                            <td><span class="badge bg-yellow-lt">{{ ucfirst($quote->status) }}</span></td>
                            <td>{{ $quote->total ?? '—' }}</td>
                            <td>{{ $quote->created_at?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">{{ __('No quotes found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
