@extends('default.panel.layout.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Repair {{ $repair->repair_number }}</h1>
        <div>
            <a href="{{ route('repair.orders.edit', $repair) }}" class="btn btn-outline-secondary">Edit</a>
            @if(!in_array($repair->repair_status, ['completed', 'verified', 'closed', 'cancelled']))
                <form action="{{ route('repair.orders.complete', $repair) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success ms-2">Mark Complete</button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8"><span class="badge bg-secondary">{{ $repair->repair_status }}</span></dd>

                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">{{ $repair->repair_type ?? '—' }}</dd>

                        <dt class="col-sm-4">Priority</dt>
                        <dd class="col-sm-8">{{ $repair->priority }}</dd>

                        <dt class="col-sm-4">Fault Category</dt>
                        <dd class="col-sm-8">{{ $repair->fault_category ?? '—' }}</dd>

                        <dt class="col-sm-4">Customer</dt>
                        <dd class="col-sm-8">{{ $repair->customer?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Premises</dt>
                        <dd class="col-sm-8">{{ $repair->premises?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Equipment</dt>
                        <dd class="col-sm-8">{{ $repair->equipment?->name ?? $repair->installedEquipment?->equipment?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Assigned To</dt>
                        <dd class="col-sm-8">{{ $repair->assignedUser?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Team</dt>
                        <dd class="col-sm-8">{{ $repair->assignedTeam?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Scheduled</dt>
                        <dd class="col-sm-8">{{ $repair->scheduled_at?->format('d M Y H:i') ?? '—' }}</dd>

                        <dt class="col-sm-4">Completed</dt>
                        <dd class="col-sm-8">{{ $repair->completed_at?->format('d M Y H:i') ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            @if($repair->diagnoses->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">Diagnoses</div>
                    <div class="card-body">
                        @foreach($repair->diagnoses as $diagnosis)
                            <div class="mb-3 pb-3 border-bottom">
                                <strong>Symptom:</strong> {{ $diagnosis->symptom }}<br>
                                @if($diagnosis->cause) <strong>Cause:</strong> {{ $diagnosis->cause }}<br> @endif
                                @if($diagnosis->recommended_action) <strong>Action:</strong> {{ $diagnosis->recommended_action }}<br> @endif
                                <div class="mt-1">
                                    @if($diagnosis->safety_flag) <span class="badge bg-danger">Safety Flag</span> @endif
                                    @if($diagnosis->requires_specialist) <span class="badge bg-warning text-dark">Specialist Required</span> @endif
                                    @if($diagnosis->requires_parts) <span class="badge bg-info text-dark">Parts Required</span> @endif
                                    @if($diagnosis->requires_quote) <span class="badge bg-secondary">Quote Required</span> @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($repair->tasks->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">Tasks</div>
                    <ul class="list-group list-group-flush">
                        @foreach($repair->tasks as $task)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $task->title }}</span>
                                <span class="badge bg-secondary">{{ $task->status }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($repair->partUsages->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">Parts Used</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Part</th><th>SKU</th><th>Qty</th><th>Unit Cost</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($repair->partUsages as $part)
                                    <tr>
                                        <td>{{ $part->part_name }}</td>
                                        <td>{{ $part->part_sku ?? '—' }}</td>
                                        <td>{{ $part->quantity }}</td>
                                        <td>{{ $part->unit_cost ? '$' . number_format($part->unit_cost, 2) : '—' }}</td>
                                        <td>
                                            @if($part->consumed) <span class="badge bg-success">Consumed</span>
                                            @elseif($part->reserved) <span class="badge bg-info text-dark">Reserved</span>
                                            @else <span class="badge bg-secondary">Pending</span> @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            {{-- Add Diagnosis --}}
            <div class="card mb-4">
                <div class="card-header">Record Diagnosis</div>
                <div class="card-body">
                    <form action="{{ route('repair.orders.diagnoses.store', $repair) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label form-label-sm">Symptom *</label>
                            <textarea name="symptom" class="form-control form-control-sm" rows="2" required></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label form-label-sm">Cause</label>
                            <input type="text" name="cause" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label form-label-sm">Recommended Action</label>
                            <input type="text" name="recommended_action" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2 d-flex gap-3 flex-wrap">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="safety_flag" value="1" id="sf"><label class="form-check-label" for="sf">Safety Flag</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="requires_specialist" value="1" id="rs"><label class="form-check-label" for="rs">Specialist</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="requires_parts" value="1" id="rp"><label class="form-check-label" for="rp">Parts</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="requires_quote" value="1" id="rq"><label class="form-check-label" for="rq">Quote</label></div>
                        </div>
                        <button class="btn btn-sm btn-primary w-100">Save Diagnosis</button>
                    </form>
                </div>
            </div>

            {{-- Apply Template --}}
            @if($templates ?? null)
            <div class="card mb-4">
                <div class="card-header">Apply Template</div>
                <div class="card-body">
                    <form action="{{ route('repair.orders.apply_template', $repair) }}" method="POST">
                        @csrf
                        <select name="repair_template_id" class="form-select form-select-sm mb-2" required>
                            <option value="">— Select template —</option>
                            @foreach($templates as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-secondary w-100">Apply</button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Coverage Summary --}}
            @php $coverage = $repair->coverageSummary(); @endphp
            <div class="card mb-4">
                <div class="card-header">Coverage</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-6">Warranty Covered</dt>
                        <dd class="col-6">{{ $coverage['is_warranty'] ? 'Yes' : 'No' }}</dd>
                        <dt class="col-6">Repair Type</dt>
                        <dd class="col-6">{{ $coverage['type'] ?? '—' }}</dd>
                        <dt class="col-6">Claim ID</dt>
                        <dd class="col-6">{{ $coverage['claim_id'] ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
