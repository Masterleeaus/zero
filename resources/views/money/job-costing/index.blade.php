@extends('layouts.app')

@section('title', 'Job Cost Allocations')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Job Cost Allocations</h1>
        <a href="{{ route('money.cost-allocations.create') }}" class="btn btn-primary">
            + New Allocation
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Job</th>
                        <th>Cost Type</th>
                        <th>Source</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Posted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allocations as $allocation)
                    <tr>
                        <td>{{ $allocation->id }}</td>
                        <td>{{ $allocation->serviceJob?->reference ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $allocation->cost_type }}</span></td>
                        <td>{{ $allocation->source_type }}</td>
                        <td>${{ number_format($allocation->amount, 2) }}</td>
                        <td>{{ $allocation->allocated_at?->format('d M Y') }}</td>
                        <td>
                            @if($allocation->posted)
                                <span class="badge bg-success">Posted</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('money.cost-allocations.show', $allocation) }}" class="btn btn-sm btn-outline-secondary">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No cost allocations recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($allocations->hasPages())
        <div class="card-footer">{{ $allocations->links() }}</div>
        @endif
    </div>
</div>
@endsection
