
<div class="row">
  <div class="col-md-5">
    <label class="form-label">Name</label>
    <input name="name" value="{{ old('name', $campaign->name ?? '') }}" class="form-control" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">From Number</label>
    <input name="from_number" value="{{ old('from_number', $campaign->from_number ?? '') }}" class="form-control" placeholder="+61...">
  </div>
  <div class="col-md-2">
    <label class="form-label">Max Attempts</label>
    <input name="max_attempts" type="number" min="1" max="10" value="{{ old('max_attempts', $campaign->max_attempts ?? 3) }}" class="form-control" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">Retry (minutes)</label>
    <input name="retry_minutes" type="number" min="1" max="1440" value="{{ old('retry_minutes', $campaign->retry_minutes ?? 60) }}" class="form-control" required>
  </div>
</div>

<div class="row mt-3">
  <div class="col-md-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
      @php $st = old('status', $campaign->status ?? 'draft'); @endphp
      <option value="draft" {{ $st=='draft'?'selected':'' }}>Draft</option>
      <option value="running" {{ $st=='running'?'selected':'' }}>Running</option>
      <option value="paused" {{ $st=='paused'?'selected':'' }}>Paused</option>
      <option value="finished" {{ $st=='finished'?'selected':'' }}>Finished</option>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Enabled</label>
    <div class="form-check mt-2">
      <input class="form-check-input" type="checkbox" name="enabled" value="1" {{ old('enabled', ($campaign->enabled ?? true)) ? 'checked' : '' }}>
      <label class="form-check-label">Active</label>
    </div>
  </div>
</div>
