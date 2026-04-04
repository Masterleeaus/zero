@extends('layouts.app')

@section('title', 'Profitability')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Profitability Overview</h1>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">By Period</div>
                <div class="card-body">
                    <form id="periodForm">
                        <div class="row g-2">
                            <div class="col">
                                <input type="date" name="from" class="form-control" value="{{ date('Y-m-01') }}">
                            </div>
                            <div class="col">
                                <input type="date" name="to" class="form-control" value="{{ date('Y-m-t') }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Calculate</button>
                            </div>
                        </div>
                    </form>
                    <div id="periodResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('periodForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = new FormData(this);
    const params = new URLSearchParams({ from: data.get('from'), to: data.get('to') });
    fetch('{{ route('money.profitability.by-period') }}?' + params, {
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('periodResult').innerHTML = `
            <dl class="row">
                <dt class="col-sm-6">Gross Revenue</dt>
                <dd class="col-sm-6">$${parseFloat(d.gross_revenue||0).toFixed(2)}</dd>
                <dt class="col-sm-6">Gross Cost</dt>
                <dd class="col-sm-6">$${parseFloat(d.gross_cost||0).toFixed(2)}</dd>
                <dt class="col-sm-6">Gross Margin</dt>
                <dd class="col-sm-6 fw-bold">$${parseFloat(d.gross_margin||0).toFixed(2)} (${parseFloat(d.margin_pct||0).toFixed(1)}%)</dd>
            </dl>`;
    });
});
</script>
@endpush
@endsection
