@php
  $canUse = auth()->check() && auth()->user()->can('titanzero.use');
  $hasIntentRoute = \Modules\Documents\Services\TitanZeroBridge::hasIntentRoute();
  $heroKey = old('titan_hero_key', request('titan_hero_key', 'safety'));
@endphp

@if ($canUse && $hasIntentRoute)
  <form method="POST" action="{{ route(Route::has('titan.zero.intent.run') ? 'titan.zero.intent.run' : 'titan.zero.heroes.ask', Route::has('titan.zero.intent.run') ? [] : [$heroKey]) }}" class="d-inline">
    @csrf
    <input type="hidden" name="return_url" value="{{ url()->current() }}">
    <input type="hidden" name="intent" value="{{ $intent ?? 'summarise_standard' }}">
    <input type="hidden" name="page[route_name]" value="{{ request()->route()?->getName() }}">
    <input type="hidden" name="page[url]" value="{{ url()->current() }}">
    <input type="hidden" name="record[record_type]" value="{{ $record_type ?? 'document' }}">
    <input type="hidden" name="record[record_id]" value="{{ $record_id ?? null }}">
    @foreach(($fields ?? []) as $k => $v)
      <input type="hidden" name="fields[{{ $k }}]" value="{{ is_scalar($v) ? $v : json_encode($v) }}">
    @endforeach

    <div class="d-flex align-items-center gap-2">
      <select name="titan_hero_key" class="form-select form-select-sm" style="max-width: 220px" onchange="this.form.action = '{{ route(Route::has('titan.zero.intent.run') ? 'titan.zero.intent.run' : 'titan.zero.heroes.ask', Route::has('titan.zero.intent.run') ? [] : ['__HERO__']) }}'.replace('__HERO__', this.value)">
        <option value="safety" @selected($heroKey==='safety')>Safety Hero</option>
        <option value="compliance" @selected($heroKey==='compliance')>Compliance Hero</option>
        <option value="ops" @selected($heroKey==='ops')>Ops Hero</option>
      </select>

      <button type="submit" class="btn btn-primary btn-sm">
        <i class="ti ti-bolt"></i>
        Ask Titan Zero
      </button>
    </div>
  </form>
@endif
