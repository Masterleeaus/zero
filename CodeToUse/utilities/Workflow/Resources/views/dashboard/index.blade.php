
<x-cards.layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="mb-0">@lang('modules.module.workflow') — Dashboard</h3>
        </div>
    </x-slot>
    <div class="row">
        <div class="col-md-4"><x-cards.widget :title="__('Active Workflows')" value="—" icon="fa-solid fa-diagram-project" /></div>
        <div class="col-md-4"><x-cards.widget :title="__('Running Jobs')" value="—" icon="fa-solid fa-gears" /></div>
        <div class="col-md-4"><x-cards.widget :title="__('Failures (7d)')" value="—" icon="fa-solid fa-triangle-exclamation" /></div>
    </div>
</x-cards.layout>
