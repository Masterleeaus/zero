@php
    use Illuminate\Support\Facades\Route;

    $u = user();
    $mods = function_exists('user_modules') ? (user_modules() ?? []) : [];
    $enabledForUser = in_array('workflow', $mods) || in_array('workflows', $mods);

    $canView = $u && method_exists($u, 'permission') ? ($u->permission('view_workflow') === 'all') : false;
@endphp

@if($enabledForUser && $canView && Route::has('workflow.account.workflows.index'))
    <x-menu-item icon="workflow" :text="__('Workflow')" :active="request()->routeIs('workflow.account.workflows.*')">
        <div class="submenu">
            <x-sub-menu-item :link="route('workflow.account.workflows.index')" :text="__('Workflows')" />
        </div>
    </x-menu-item>
@endif
