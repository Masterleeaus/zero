<div class="row">
  <div class="col-md-3"><x-card><x-slot name="body"><div class="small text-muted">@lang('propertymanagement::app.tags')</div><div>{{ $counts['tags'] ?? 0 }}</div></x-slot></x-card></div>
  <div class="col-md-3"><x-card><x-slot name="body"><div class="small text-muted">@lang('propertymanagement::app.rooms')</div><div>{{ $counts['rooms'] ?? 0 }}</div></x-slot></x-card></div>
  <div class="col-md-3"><x-card><x-slot name="body"><div class="small text-muted">@lang('propertymanagement::app.hazards')</div><div>{{ $counts['hazards'] ?? 0 }}</div></x-slot></x-card></div>
  <div class="col-md-3"><x-card><x-slot name="body"><div class="small text-muted">@lang('propertymanagement::app.assets')</div><div>{{ $counts['assets'] ?? 0 }}</div></x-slot></x-card></div>
</div>
