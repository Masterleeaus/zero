<div class="table-responsive p-20">
    <x-table class="table-bordered">
        <x-slot name="thead">
            <th>@lang('units::modules.unit.floorcode')</th>
            <th>@lang('units::modules.unit.floorName')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($floors as $key => $floor)
            <tr class="row{{ $floor->id }}">
                <td>{{ mb_ucwords($floor->floor_code) }}</td>
                <td>{{ mb_ucwords($floor->floor_name) }}</td>
                <td class="text-right">
                    <div class="task_view">
                        <a class="task_view_more d-flex align-items-center justify-content-center edit-floor" href="javascript:;" data-floor-id="{{ $floor->id }}" >
                            <i class="fa fa-edit icons mr-2"></i>  @lang('app.edit')
                        </a>
                    </div>
                    <div class="task_view">
                        <a class="delete-category task_view_more d-flex align-items-center justify-content-center delete-floor" href="javascript:;" data-floor-id="{{ $floor->id }}"  >
                            <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">
                    <x-cards.no-record icon="list" :message="__('No Floor Added')" />
                </td>
            </tr>
        @endforelse
    </x-table>
</div>
