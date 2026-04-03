{{--
    Shared form partial for RepairOrder create/edit views.
    Variables expected: $repair, $statuses, $priorities, $repairTypes,
    $customers, $premises, $equipment, $assignees, $teams, $templates,
    $serviceJobs, $warrantyClaims
--}}
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Core Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        @foreach($priorities as $p)
                            <option value="{{ $p }}" @selected(old('priority', $repair->priority) === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Repair Type</label>
                    <select name="repair_type" class="form-select">
                        <option value="">— None —</option>
                        @foreach($repairTypes as $t)
                            <option value="{{ $t }}" @selected(old('repair_type', $repair->repair_type) === $t)>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="repair_status" class="form-select">
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" @selected(old('repair_status', $repair->repair_status) === $s)>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fault Category</label>
                    <input type="text" name="fault_category" class="form-control" value="{{ old('fault_category', $repair->fault_category) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Severity</label>
                    <input type="text" name="severity" class="form-control" value="{{ old('severity', $repair->severity) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Scheduled At</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control"
                        value="{{ old('scheduled_at', $repair->scheduled_at?->format('Y-m-d\TH:i')) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Assignment</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($customers as $id => $name)
                            <option value="{{ $id }}" @selected(old('customer_id', $repair->customer_id) == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Premises</label>
                    <select name="premises_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($premises as $id => $name)
                            <option value="{{ $id }}" @selected(old('premises_id', $repair->premises_id) == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assigned User</label>
                    <select name="assigned_user_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($assignees as $id => $name)
                            <option value="{{ $id }}" @selected(old('assigned_user_id', $repair->assigned_user_id) == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Team</label>
                    <select name="assigned_team_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($teams as $id => $name)
                            <option value="{{ $id }}" @selected(old('assigned_team_id', $repair->assigned_team_id) == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">Equipment Linkage</div>
            <div class="card-body row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Equipment</label>
                    <select name="equipment_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($equipment as $id => $name)
                            <option value="{{ $id }}" @selected(old('equipment_id', $repair->equipment_id) == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Service Job</label>
                    <select name="service_job_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($serviceJobs as $id => $title)
                            <option value="{{ $id }}" @selected(old('service_job_id', $repair->service_job_id) == $id)>{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Warranty Claim</label>
                    <select name="warranty_claim_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($warrantyClaims as $id => $ref)
                            <option value="{{ $id }}" @selected(old('warranty_claim_id', $repair->warranty_claim_id) == $id)>{{ $ref }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Template</label>
                    <select name="repair_template_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($templates as $id => $name)
                            <option value="{{ $id }}" @selected(old('repair_template_id', $repair->repair_template_id) == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">Summary</div>
            <div class="card-body row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Diagnosis Summary</label>
                    <textarea name="diagnosis_summary" class="form-control" rows="3">{{ old('diagnosis_summary', $repair->diagnosis_summary) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Resolution Summary</label>
                    <textarea name="resolution_summary" class="form-control" rows="3">{{ old('resolution_summary', $repair->resolution_summary) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">Flags</div>
            <div class="card-body d-flex gap-4 flex-wrap">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="requires_parts" value="1" id="req_parts"
                        @checked(old('requires_parts', $repair->requires_parts))>
                    <label class="form-check-label" for="req_parts">Requires Parts</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="requires_followup" value="1" id="req_followup"
                        @checked(old('requires_followup', $repair->requires_followup))>
                    <label class="form-check-label" for="req_followup">Requires Follow-up</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="requires_quote" value="1" id="req_quote"
                        @checked(old('requires_quote', $repair->requires_quote))>
                    <label class="form-check-label" for="req_quote">Requires Quote</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="requires_return_visit" value="1" id="req_return"
                        @checked(old('requires_return_visit', $repair->requires_return_visit))>
                    <label class="form-check-label" for="req_return">Requires Return Visit</label>
                </div>
            </div>
        </div>
    </div>
</div>
