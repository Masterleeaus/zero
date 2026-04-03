@extends('default.panel.layout.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Repair Orders</h1>
        <a href="{{ route('repair.orders.create') }}" class="btn btn-primary">New Repair</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Customer</th>
                        <th>Equipment</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Scheduled</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($repairs as $repair)
                        <tr>
                            <td>
                                <a href="{{ route('repair.orders.show', $repair) }}">
                                    {{ $repair->repair_number }}
                                </a>
                            </td>
                            <td>{{ $repair->customer?->name ?? '—' }}</td>
                            <td>{{ $repair->equipment?->name ?? $repair->installedEquipment?->equipment?->name ?? '—' }}</td>
                            <td>{{ $repair->repair_type ?? '—' }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $repair->repair_status }}</span>
                            </td>
                            <td>{{ $repair->priority }}</td>
                            <td>{{ $repair->scheduled_at?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('repair.orders.edit', $repair) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No repair orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($repairs->hasPages())
            <div class="card-footer">{{ $repairs->links() }}</div>
        @endif
    </div>
</div>
@endsection
