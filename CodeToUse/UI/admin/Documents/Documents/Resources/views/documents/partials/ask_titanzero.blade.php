@php
  $canUse = auth()->check() && auth()->user()->can('titanzero.use');
  $heroKey = old('titan_hero_key', request('titan_hero_key', 'safety'));

  $useIntentRun = \Illuminate\Support\Facades\Route::has('titan.zero.intent.run');
  $useHeroesAsk = \Illuminate\Support\Facades\Route::has('titan.zero.heroes.ask');
@endphp

@if ($canUse && ($useIntentRun || $useHeroesAsk))
  @php
    $actionRoute = $useIntentRun ? 'titan.zero.intent.run' : 'titan.zero.heroes.ask';
    $actionParams = $useIntentRun ? [] : [$heroKey];
  @endphp

  <form id="tzAskForm" method="POST" action="{{ route($actionRoute, $actionParams) }}" class="d-inline">
    @csrf

    <input type="hidden" name="return_url" value="{{ $return_url ?? url()->current() }}">
    <input type="hidden" name="intent" value="{{ $intent ?? 'check_compliance' }}">

    <input type="hidden" name="page[route_name]" value="{{ request()->route()?->getName() }}">
    <input type="hidden" name="page[url]" value="{{ url()->current() }}">

    <input type="hidden" name="record[record_type]" value="{{ $record_type ?? 'document' }}">
    <input type="hidden" name="record[record_id]" value="{{ $record_id ?? null }}">

    @foreach(($fields ?? []) as $k => $v)
      <input type="hidden" name="fields[{{ $k }}]" value="{{ is_scalar($v) ? $v : json_encode($v) }}">
    @endforeach

    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
    <input type="hidden" name="company_id" value="{{ auth()->user()->company_id ?? (function_exists('company') && company() ? company()->id : null) }}">

    <div class="d-flex align-items-center gap-2">
      <select name="titan_hero_key"
              class="form-select form-select-sm"
              style="max-width: 200px"
              @if(!$useIntentRun) onchange="document.getElementById('tzAskForm').action = '{{ route('titan.zero.heroes.ask', ['__HERO__']) }}'.replace('__HERO__', this.value);" @endif>
        <option value="safety" @selected($heroKey==='safety')>Safety Hero</option>
        <option value="compliance" @selected($heroKey==='compliance')>Compliance Hero</option>
        <option value="ops" @selected($heroKey==='ops')>Ops Hero</option>
      </select>

      <button type="submit" class="btn btn-outline-primary btn-sm">
        <i class="ti ti-sparkles"></i>
        Ask Titan Hero
      </button>
    </div>
  </form>
@endif