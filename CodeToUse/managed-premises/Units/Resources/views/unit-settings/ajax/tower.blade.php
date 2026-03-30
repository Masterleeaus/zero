<div class="table-responsive p-20">
    <x-table class="table-bordered">
        <x-slot name="thead">
            <th>@lang('units::modules.unit.towercode')</th>
            <th>@lang('units::modules.unit.towerName')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($towers as $key => $tower)
            <tr class="row{{ $tower->id }}">
                <td>{{ mb_ucwords($tower->tower_code) }}</td>
                <td>{{ mb_ucwords($tower->tower_name) }}</td>
                <td class="text-right">
                    <div class="task_view">
                        <a class="task_view_more d-flex align-items-center justify-content-center edit-tower" href="javascript:;" data-tower-id="{{ $tower->id }}" >
                            <i class="fa fa-edit icons mr-2"></i>  @lang('app.edit')
                        </a>
                    </div>
                    <div class="task_view">
                        <a class="delete-category task_view_more d-flex align-items-center justify-content-center delete-tower" href="javascript:;" data-tower-id="{{ $tower->id }}"  >
                            <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">
                    <x-cards.no-record icon="list" :message="__('No Tower Added')" />
                </td>
            </tr>
        @endforelse
    </x-table>
</div>
