<div class="row g-2">
  <div class="col-md-3">
    <label class="form-label">Cost</label>
    <input type="number" step="0.01" name="fsm_cost" value="{{ old('fsm_cost', $item->fsm_cost ?? '') }}" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">Markup %</label>
    <input type="number" step="0.01" name="fsm_markup_percent" value="{{ old('fsm_markup_percent', $item->fsm_markup_percent ?? '') }}" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">Unit</label>
    <input type="text" name="fsm_unit" value="{{ old('fsm_unit', $item->fsm_unit ?? '') }}" class="form-control" placeholder="e.g. m, pcs, box">
  </div>
  <div class="col-md-3">
    <label class="form-label">Default Supplier</label>
    <select name="fsm_default_supplier_id" class="form-select">
      <option value="">— Select —</option>
      @foreach(($suppliers ?? []) as $s)
        <option value="{{ $s->id }}" @selected(($item->fsm_default_supplier_id ?? null) == $s->id)>{{ $s->name }}</option>
      @endforeach
    </select>
  </div>
</div>

<p id="pricePreview" class="text-muted mt-2"></p>
