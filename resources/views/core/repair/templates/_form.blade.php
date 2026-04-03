{{--
    Shared form partial for RepairTemplate create/edit views.
    Variables expected: $template, $categories
--}}
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header">Template Details</div>
    <div class="card-body row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Category</label>
            <select name="template_category" class="form-select">
                <option value="">— None —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected(old('template_category', $template->template_category) === $cat)>
                        {{ ucwords(str_replace('_', ' ', $cat)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Equipment Type</label>
            <input type="text" name="equipment_type" class="form-control" value="{{ old('equipment_type', $template->equipment_type) }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Fault Type</label>
            <input type="text" name="fault_type" class="form-control" value="{{ old('fault_type', $template->fault_type) }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Manufacturer</label>
            <input type="text" name="manufacturer" class="form-control" value="{{ old('manufacturer', $template->manufacturer) }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Service Category</label>
            <input type="text" name="service_category" class="form-control" value="{{ old('service_category', $template->service_category) }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Estimated Duration (minutes)</label>
            <input type="number" name="estimated_duration" class="form-control" min="0"
                value="{{ old('estimated_duration', $template->estimated_duration) }}">
        </div>
        <div class="col-md-6 mb-3 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="active" value="1" id="active"
                    @checked(old('active', $template->active ?? true))>
                <label class="form-check-label" for="active">Active</label>
            </div>
        </div>
        <div class="col-12 mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $template->description) }}</textarea>
        </div>
        <div class="col-12 mb-3">
            <label class="form-label">Safety Notes</label>
            <textarea name="safety_notes" class="form-control" rows="2">{{ old('safety_notes', $template->safety_notes) }}</textarea>
        </div>
    </div>
</div>
