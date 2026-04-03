@php
    use Illuminate\Support\Facades\Route;
    $u = user();
    $canView = $u && method_exists($u, 'is_superadmin') ? $u->is_superadmin : false;
@endphp

@if($canView && Route::has('workflow.admin.workflows.index'))
    <x-menu-item icon="workflow" :text="__('Workflow')" :active="request()->routeIs('workflow.admin.workflows.*')">
        <div class="submenu">
            <x-sub-menu-item :link="route('workflow.admin.workflows.index')" :text="__('Workflows')" />
        </div>
    </x-menu-item>
@endif
