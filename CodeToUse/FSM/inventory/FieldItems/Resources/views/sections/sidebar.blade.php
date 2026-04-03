@if (in_array('items', user_modules()))
    <li class="{{ request()->routeIs('items.*') ? 'active' : '' }}">
        <a href="{{ route('items.index') }}">
            <i class="fa fa-box"></i>
            <span>@lang('fielditems::app.menu.items')</span>
        </a>
    </li>
@endif
