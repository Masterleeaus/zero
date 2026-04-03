
<div class="row">
  <div class="col-md-5">
    <label class="form-label">Name</label>
    <input name="name" value="{{ old('name', $menu->name ?? '') }}" class="form-control" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Repeat Count</label>
    <input name="repeat_count" type="number" min="1" max="5" value="{{ old('repeat_count', $menu->repeat_count ?? 2) }}" class="form-control" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">Timeout (sec)</label>
    <input name="timeout_seconds" type="number" min="3" max="20" value="{{ old('timeout_seconds', $menu->timeout_seconds ?? 6) }}" class="form-control" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">Enabled</label>
    <div class="form-check mt-2">
      <input class="form-check-input" type="checkbox" name="enabled" value="1" {{ old('enabled', ($menu->enabled ?? true)) ? 'checked' : '' }}>
      <label class="form-check-label">Active</label>
    </div>
  </div>
</div>

<div class="mt-3">
  <label class="form-label">Greeting Text (what caller hears)</label>
  <textarea name="greeting_text" class="form-control" rows="3">{{ old('greeting_text', $menu->greeting_text ?? '') }}</textarea>
</div>
