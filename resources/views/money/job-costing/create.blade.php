@extends('layouts.app')

@section('title', 'New Cost Allocation')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('money.cost-allocations.index') }}" class="text-muted small">&larr; All Allocations</a>
        <h1 class="h3 mt-1">New Cost Allocation</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('money.cost-allocations.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Service Job</label>
                        <select name="service_job_id" class="form-select">
                            <option value="">— No specific job —</option>
                            @foreach($jobs as $job)
                            <option value="{{ $job->id }}" {{ old('service_job_id') == $job->id ? 'selected' : '' }}>
                                {{ $job->reference }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Cost Type <span class="text-danger">*</span></label>
                        <select name="cost_type" class="form-select" required>
                            @foreach($costTypes as $type)
                            <option value="{{ $type }}" {{ old('cost_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Source Type <span class="text-danger">*</span></label>
                        <select name="source_type" class="form-select" required>
                            @foreach($sourceTypes as $type)
                            <option value="{{ $type }}" {{ old('source_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="amount" step="0.01" min="0"
                                class="form-control @error('amount') is-invalid @enderror"
                                value="{{ old('amount') }}" required>
                        </div>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" step="0.0001" min="0"
                            class="form-control" value="{{ old('quantity') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Unit Cost</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="unit_cost" step="0.01" min="0"
                                class="form-control" value="{{ old('unit_cost') }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Allocated Date <span class="text-danger">*</span></label>
                        <input type="date" name="allocated_at"
                            class="form-control @error('allocated_at') is-invalid @enderror"
                            value="{{ old('allocated_at', date('Y-m-d')) }}" required>
                        @error('allocated_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Allocation</button>
                    <a href="{{ route('money.cost-allocations.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
