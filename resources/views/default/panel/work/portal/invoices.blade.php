@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('My Invoices'))

@section('content')
<div class="container-xl">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">{{ __('Invoices') }}</h2>
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
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->reference ?? '#'.$invoice->id }}</td>
                            <td><span class="badge bg-blue-lt">{{ ucfirst($invoice->status) }}</span></td>
                            <td>{{ $invoice->total ?? '—' }}</td>
                            <td>{{ $invoice->created_at?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">{{ __('No invoices found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
