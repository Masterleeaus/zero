<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('propertymanagement.properties.show') ? 'active' : '' }}" href="{{ route('propertymanagement.properties.show', $property->id) }}">{{ __('propertymanagement::pm.property') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('propertymanagement.plans.*') ? 'active' : '' }}" href="{{ route('propertymanagement.plans.index', $property->id) }}">{{ __('propertymanagement::pm.service_plans') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('propertymanagement.visits.*') ? 'active' : '' }}" href="{{ route('propertymanagement.visits.index', $property->id) }}">{{ __('propertymanagement::pm.visits') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('propertymanagement.inspections.*') ? 'active' : '' }}" href="{{ route('propertymanagement.inspections.index', $property->id) }}">{{ __('propertymanagement::pm.inspections') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('propertymanagement.documents.*') ? 'active' : '' }}" href="{{ route('propertymanagement.documents.index', $property->id) }}">{{ __('propertymanagement::pm.documents') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('propertymanagement.approvals.*') ? 'active' : '' }}" href="{{ route('propertymanagement.approvals.index', $property->id) }}">{{ __('propertymanagement::pm.approvals') }}</a></li>
</ul>
