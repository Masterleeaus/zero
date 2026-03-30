<div class="table-responsive p-20">
    <x-table class="table-bordered">
        <x-slot name="thead">
            <th>@lang('houses::modules.house.typehouseCode')</th>
            <th>@lang('houses::modules.house.typehouseName')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($typehouses as $key => $typehouse)
            <tr class="row{{ $typehouse->id }}">
                <td>{{ mb_ucwords($typehouse->typehouse_code) }}</td>
                <td>{{ mb_ucwords($typehouse->typehouse_name) }}</td>
                <td class="text-right">
                    <div class="task_view">
                        <a class="task_view_more d-flex align-items-center justify-content-center edit-typehouse" href="javascript:;" data-typehouse-id="{{ $typehouse->id }}" >
                            <i class="fa fa-edit icons mr-2"></i>  @lang('app.edit')
                        </a>
                    </div>
                    <div class="task_view">
                        <a class="delete-category task_view_more d-flex align-items-center justify-content-center delete-typehouse" href="javascript:;" data-typehouse-id="{{ $typehouse->id }}"  >
                            <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">
                    <x-cards.no-record icon="list" :message="__('No Type House Added')" />
                </td>
            </tr>
        @endforelse
    </x-table>
</div>
