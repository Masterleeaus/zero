@php $brand = config('contracts.brand'); @endphp
<div class="d-flex align-items-center gap-3 mb-3">
  @if(!empty($brand['logo_url'])) <img src="{{ $brand['logo_url'] }}" style="height:48px"> @endif
  <div>
    <h4 class="mb-0">{{ $brand['company_name'] ?? '' }}</h4>
  </div>
</div>
<hr class="my-3">
