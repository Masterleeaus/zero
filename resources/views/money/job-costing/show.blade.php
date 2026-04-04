@extends('layouts.app')

@section('title', 'Cost Allocation #' . $allocation->id)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('money.cost-allocations.index') }}" class="text-muted small">&larr; All Allocations</a>
        <h1 class="h3 mt-1">Cost Allocation #{{ $allocation->id }}</h1>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Cost Type</dt>
                        <dd class="col-sm-8"><span class="badge bg-secondary">{{ $allocation->cost_type }}</span></dd>

                        <dt class="col-sm-4">Source Type</dt>
                        <dd class="col-sm-8">{{ $allocation->source_type }}</dd>

                        <dt class="col-sm-4">Amount</dt>
                        <dd class="col-sm-8 fw-bold">${{ number_format($allocation->amount, 2) }}</dd>

                        <dt class="col-sm-4">Job</dt>
                        <dd class="col-sm-8">{{ $allocation->serviceJob?->reference ?? '—' }}</dd>

                        <dt class="col-sm-4">Allocated At</dt>
                        <dd class="col-sm-8">{{ $allocation->allocated_at?->format('d M Y') }}</dd>

                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $allocation->description ?? '—' }}</dd>

                        <dt class="col-sm-4">Posted</dt>
                        <dd class="col-sm-8">
                            @if($allocation->posted)
                                <span class="badge bg-success">Posted {{ $allocation->posted_at?->format('d M Y') }}</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
