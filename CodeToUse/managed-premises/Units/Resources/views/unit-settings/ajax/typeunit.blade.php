<div class="table-responsive p-20">
    <x-table class="table-bordered">
        <x-slot name="thead">
            <th>@lang('units::modules.unit.typeunitCode')</th>
            <th>@lang('units::modules.unit.typeunitName')</th>
            <th class="text-right">@lang('app.action')</th>
        </x-slot>

        @forelse($typeunits as $key => $typeunit)
            <tr class="row{{ $typeunit->id }}">
                <td>{{ mb_ucwords($typeunit->typeunit_code) }}</td>
                <td>{{ mb_ucwords($typeunit->typeunit_name) }}</td>
                <td class="text-right">
                    <div class="task_view">
                        <a class="task_view_more d-flex align-items-center justify-content-center edit-typeunit" href="javascript:;" data-typeunit-id="{{ $typeunit->id }}" >
                            <i class="fa fa-edit icons mr-2"></i>  @lang('app.edit')
                        </a>
                    </div>
                    <div class="task_view">
                        <a class="delete-category task_view_more d-flex align-items-center justify-content-center delete-typeunit" href="javascript:;" data-typeunit-id="{{ $typeunit->id }}"  >
                            <i class="fa fa-trash icons mr-2"></i> @lang('app.delete')
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">
                    <x-cards.no-record icon="list" :message="__('No Type Unit Added')" />
                </td>
            </tr>
        @endforelse
    </x-table>
</div>
