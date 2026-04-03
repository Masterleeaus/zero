
<div class="row">
  <div class="col-md-5">
    <label class="form-label">Name</label>
    <input name="name" value="{{ old('name', $group->name ?? '') }}" class="form-control" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Strategy</label>
    <select name="strategy" class="form-select">
      <option value="simultaneous" {{ old('strategy', $group->strategy ?? 'simultaneous')=='simultaneous'?'selected':'' }}>Simultaneous</option>
      <option value="round_robin" {{ old('strategy', $group->strategy ?? '')=='round_robin'?'selected':'' }}>Round robin</option>
      <option value="sequential" {{ old('strategy', $group->strategy ?? '')=='sequential'?'selected':'' }}>Sequential</option>
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label">Timeout (sec)</label>
    <input name="timeout_seconds" type="number" min="5" max="60" value="{{ old('timeout_seconds', $group->timeout_seconds ?? 25) }}" class="form-control" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">Enabled</label>
    <div class="form-check mt-2">
      <input class="form-check-input" type="checkbox" name="enabled" value="1" {{ old('enabled', ($group->enabled ?? true)) ? 'checked' : '' }}>
      <label class="form-check-label">Active</label>
    </div>
  </div>
</div>
