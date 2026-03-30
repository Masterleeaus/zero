<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('managedpremises.properties.show') ? 'active' : '' }}" href="{{ route('managedpremises.properties.show', $property->id) }}">{{ __('managedpremises::pm.property') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('managedpremises.plans.*') ? 'active' : '' }}" href="{{ route('managedpremises.plans.index', $property->id) }}">{{ __('managedpremises::pm.service_plans') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('managedpremises.visits.*') ? 'active' : '' }}" href="{{ route('managedpremises.visits.index', $property->id) }}">{{ __('managedpremises::pm.visits') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('managedpremises.inspections.*') ? 'active' : '' }}" href="{{ route('managedpremises.inspections.index', $property->id) }}">{{ __('managedpremises::pm.inspections') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('managedpremises.documents.*') ? 'active' : '' }}" href="{{ route('managedpremises.documents.index', $property->id) }}">{{ __('managedpremises::pm.documents') }}</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('managedpremises.approvals.*') ? 'active' : '' }}" href="{{ route('managedpremises.approvals.index', $property->id) }}">{{ __('managedpremises::pm.approvals') }}</a></li>
</ul>
