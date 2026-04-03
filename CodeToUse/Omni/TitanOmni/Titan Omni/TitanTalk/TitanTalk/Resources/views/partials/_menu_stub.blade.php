{{-- Drop this into your sidebar/menu blade --}}
@php
  $companyId = (int) (tenant()->id ?? company()->id ?? 0);
  $hasAIConverse = true;
  try {
    $hasAIConverse = DB::table('module_settings')
      ->where('company_id', $companyId)
      ->where('module_name', 'aiconverse')
      ->where('status', 'active')
      ->exists();
  } catch (\Throwable $e) { $hasAIConverse = true; }
@endphp

@if ($hasAIConverse)
  <li class="{{ request()->routeIs('titantalk.*') ? 'active' : '' }}">
    <a href="{{ route('titantalk.index') }}">
      <i class="fa fa-comments"></i> <span>AIConverse</span>
    </a>
  </li>
@endif
