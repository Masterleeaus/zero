@extends('default.panel.layout.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $template->name }}</h1>
        <div>
            <a href="{{ route('repair.templates.edit', $template) }}" class="btn btn-outline-secondary">Edit</a>
            <form action="{{ route('repair.templates.destroy', $template) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger ms-2"
                    onclick="return confirm('Delete this template?')">Delete</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Template Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Category</dt>
                        <dd class="col-sm-7">{{ $template->template_category ?? '—' }}</dd>
                        <dt class="col-sm-5">Equipment Type</dt>
                        <dd class="col-sm-7">{{ $template->equipment_type ?? '—' }}</dd>
                        <dt class="col-sm-5">Fault Type</dt>
                        <dd class="col-sm-7">{{ $template->fault_type ?? '—' }}</dd>
                        <dt class="col-sm-5">Manufacturer</dt>
                        <dd class="col-sm-7">{{ $template->manufacturer ?? '—' }}</dd>
                        <dt class="col-sm-5">Service Category</dt>
                        <dd class="col-sm-7">{{ $template->service_category ?? '—' }}</dd>
                        <dt class="col-sm-5">Est. Duration</dt>
                        <dd class="col-sm-7">{{ $template->estimateDuration() }} min</dd>
                        <dt class="col-sm-5">Active</dt>
                        <dd class="col-sm-7">{{ $template->active ? 'Yes' : 'No' }}</dd>
                    </dl>
                    @if($template->description)
                        <hr>
                        <p class="mb-0 text-muted small">{{ $template->description }}</p>
                    @endif
                    @if($template->safety_notes)
                        <div class="alert alert-warning mt-3 mb-0"><strong>Safety:</strong> {{ $template->safety_notes }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            @if($template->steps->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">Steps ({{ $template->steps->count() }})</div>
                    <ol class="list-group list-group-flush list-group-numbered">
                        @foreach($template->steps as $step)
                            <li class="list-group-item">
                                <strong>{{ $step->title }}</strong>
                                @if($step->description) <br><small class="text-muted">{{ $step->description }}</small> @endif
                                <div class="mt-1">
                                    @if($step->safety_flag) <span class="badge bg-danger">Safety</span> @endif
                                    @if($step->requires_parts) <span class="badge bg-info text-dark">Parts</span> @endif
                                    @if($step->estimated_duration) <span class="badge bg-light text-dark">{{ $step->estimated_duration }} min</span> @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            @if($template->parts->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">Parts ({{ $template->parts->count() }})</div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Part</th><th>SKU</th><th>Qty</th><th>Unit Cost</th></tr></thead>
                            <tbody>
                                @foreach($template->parts as $part)
                                    <tr>
                                        <td>{{ $part->part_name }} @if($part->optional) <span class="badge bg-light text-dark">Optional</span> @endif</td>
                                        <td>{{ $part->part_sku ?? '—' }}</td>
                                        <td>{{ $part->quantity }}</td>
                                        <td>{{ $part->unit_cost ? '$' . number_format($part->unit_cost, 2) : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
