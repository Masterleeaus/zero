@php
// Sidebar must never throw.
// Avoid DB calls or undefined variables here.
@endphp

@if(function_exists('user') && user() && user()->can('propertymanagement.view') || user()->can('propertymanagement.create') || user()->can('propertymanagement.update') || user()->can('propertymanagement.delete'))
<li class="nav-item">
    <a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('propertymanagement.dashboard') ? route('propertymanagement.dashboard') : '#' }}">
        <i class="fa fa-building"></i>
        <span>@lang('propertymanagement::app.module_name')</span>
    </a>

    <ul class="nav flex-column ml-3">
        <li class="nav-item">
            <a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('propertymanagement.properties.index') ? route('propertymanagement.properties.index') : '#' }}">@lang('propertymanagement::app.properties')</a>
        </li>
        @if(user()->can('propertymanagement.settings'))
        <li class="nav-item">
            <a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('propertymanagement.settings.index') ? route('propertymanagement.settings.index') : '#' }}">@lang('propertymanagement::app.settings')</a>
        </li>
        @endif
    
<li class="nav-item"><a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('propertymanagement.calendar') ? route('propertymanagement.calendar.index') : '#' }}"><i class="ti ti-calendar"></i> {{ __('propertymanagement::pm.calendar') }}</a></li>
</ul>
</li>
@endif
