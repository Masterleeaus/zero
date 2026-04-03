@php
$editPermission = user()->permission('edit_item');
$deletePermission = user()->permission('delete_item');
@endphp
<div id="item-detail-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-lg-10 col-10">
                            <h3 class="heading-h1 mb-3">@lang('app.menu.items') @lang('app.details')</h3>
                        </div>
                        <div class="col-lg-2 col-2 text-right">
                            @if (
                                ($editPermission == 'all' || ($editPermission == 'added' && $item->added_by == user()->id))
                                || ($deletePermission == 'all' || ($deletePermission == 'added' && $item->added_by == user()->id))
                                )
                                <div class="dropdown">
                                    <button
                                        class="btn btn-lg f-14 px-2 py-1 text-dark-grey text-capitalize rounded  dropdown-toggle"
                                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                        aria-labelledby="dropdownMenuLink" tabindex="0">
                                        @if ($editPermission == 'all' || ($editPermission == 'added' && $item->added_by == user()->id))
                                            <a class="dropdown-item openRightModal"
                                                href="{{ route('items.edit', $item->id) }}">@lang('app.edit')
                                            </a>
                                        @endif

                                        @if ($deletePermission == 'all' || ($deletePermission == 'added' && $item->added_by == user()->id))
                                            <a class="dropdown-item delete-item"
                                                data-item-id="{{ $item->id }}">@lang('app.delete')</a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <x-cards.data-row :label="__('app.name')" :value="$item->name ?? '--'" />
                            <x-cards.data-row :label="__('app.price')" :value="$item->price ? currency_format($item->price) : '--'" />
                            <x-cards.data-row :label="__('modules.invoices.tax')" :value="!empty($taxValue) ? strtoupper($taxValue) : '--'" />
                            <x-cards.data-row :label="__('app.qty_aktual')" :value="ucfirst($item->qty_aktual ?? '--')" />
                            <x-cards.data-row :label="__('app.qty_minimal')" :value="ucfirst($item->qty_minimal ?? '--')" />
                                <x-cards.data-row :label="__('modules.unitType.unitType')" :value="ucfirst($item->unit->unit_type ?? '--')" />
                            <x-cards.data-row :label="__('app.hsnSac')" :value="$item->hsn_sac_code ?? '--'" />
                            <x-cards.data-row :label="__('modules.itemCategory.itemCategory')"
                                :value="$item->category->category_name ?? '--'" />
                            <x-cards.data-row :label="__('modules.itemCategory.itemSubCategory')"
                                :value="$item->subCategory->category_name ?? '--'" />

                            @if (!in_array('client', user_roles()))
                                <x-cards.data-row :label="__('app.purchaseAllow')" :value="($item->allow_purchase) ? '<span class=\'badge badge-success\'>'.
                                    __('app.yes').' </span>': '<span class=\'badge badge-danger\'>'.
                                        __('app.no').' </span>'" />
                            @endif
                            <x-cards.data-row :label="__('app.downloadable')" :value="($item->downloadable) ? '<span class=\'badge badge-success\'>'.
                                __('app.yes').' </span>': '<span class=\'badge badge-danger\'>'.
                                    __('app.no').' </span>'" />
                            @if ($item->downloadable && !in_array('client', user_roles()))
                                <x-cards.data-row :label="__('app.downloadableFile')"
                                    :value="'<a href='.$item->download_file_url.' download>'.$item->download_file_url.'</a>'" />

                            @endif
                            <x-cards.data-row :label="__('app.description')" :value="!empty($item->description) ? $item->description : '--'"
                                html="true" />

                            @if ($item->files)
                                <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                                    <p class="mb-0 text-lightest f-14 w-30 text-capitalize">{{ __('modules.itemImage') }}</p>
                                    <p class="mb-0 text-dark-grey f-14 w-70 text-wrap">
                                        @foreach ($item->files as $file)
                                            <a href="javascript:;" class="img-lightbox" data-image-url="{{ $file->file_url }}">
                                                <img src="{{ $file->file_url }}" width="80" height="80" class="img-thumbnail">
                                            </a>
                                        @endforeach
                                    </p>
                                </div>
                            @endif
                            {{-- Custom fields data --}}
                            {{-- <x-forms.custom-field-show :fields="$fields" :model="$item"></x-forms.custom-field-show> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').on('click', '.delete-item', function() {
        var id = $(this).data('item-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                let url = "{{ route('items.destroy', ':id') }}";
                url = url.replace(':id', id);

                const token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            window.location.href = response.redirectUrl;
                        }
                    }
                });
            }
        });
    });

</script>
