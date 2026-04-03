
<div class="row">
  <div class="col-md-4">
    <label class="form-label">Phone Number (DID)</label>
    <input name="phone_number" value="{{ old('phone_number', $number->phone_number ?? '') }}" class="form-control" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Label</label>
    <input name="label" value="{{ old('label', $number->label ?? '') }}" class="form-control">
  </div>
  <div class="col-md-4">
    <label class="form-label">Enabled</label>
    <div class="form-check mt-2">
      <input class="form-check-input" type="checkbox" name="enabled" value="1" {{ old('enabled', ($number->enabled ?? true)) ? 'checked' : '' }}>
      <label class="form-check-label">Active</label>
    </div>
  </div>
</div>

<div class="row mt-3">
  <div class="col-md-4">
    <label class="form-label">Routing Mode</label>
    <select name="mode" class="form-select">
      <option value="ring_group" {{ old('mode', $number->mode ?? 'ring_group')=='ring_group'?'selected':'' }}>Ring Group</option>
      <option value="ivr" {{ old('mode', $number->mode ?? 'ring_group')=='ivr'?'selected':'' }}>IVR Menu</option>
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">Target ID</label>
    <input name="target_id" value="{{ old('target_id', $number->target_id ?? '') }}" class="form-control" placeholder="RingGroup ID or IVR Menu ID">
  </div>
  <div class="col-md-4">
    <label class="form-label">Business Hours Only</label>
    <div class="form-check mt-2">
      <input class="form-check-input" type="checkbox" name="business_hours_only" value="1" {{ old('business_hours_only', ($number->business_hours_only ?? false)) ? 'checked' : '' }}>
      <label class="form-check-label">Apply after-hours routing</label>
    </div>
  </div>
</div>

<div class="row mt-3">
  <div class="col-md-4">
    <label class="form-label">After Hours Mode</label>
    <select name="after_hours_mode" class="form-select">
      <option value="">(none)</option>
      <option value="ring_group" {{ old('after_hours_mode', $number->after_hours_mode ?? '')=='ring_group'?'selected':'' }}>Ring Group</option>
      <option value="ivr" {{ old('after_hours_mode', $number->after_hours_mode ?? '')=='ivr'?'selected':'' }}>IVR Menu</option>
      <option value="voicemail" {{ old('after_hours_mode', $number->after_hours_mode ?? '')=='voicemail'?'selected':'' }}>Voicemail</option>
      <option value="hangup" {{ old('after_hours_mode', $number->after_hours_mode ?? '')=='hangup'?'selected':'' }}>Hangup</option>
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">After Hours Target ID</label>
    <input name="after_hours_target_id" value="{{ old('after_hours_target_id', $number->after_hours_target_id ?? '') }}" class="form-control">
  </div>
</div>
