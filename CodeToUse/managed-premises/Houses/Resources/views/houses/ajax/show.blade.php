@php
$editHousePermission = user()->permission('edit_house');
$deleteHousePermission = user()->permission('delete_house');
@endphp
<div id="leave-detail-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">@lang('houses::app.menu.house') @lang('app.details')</h3>
                        </div>
                        <div class="col-md-2 col-2 text-right">
                            <div class="dropdown">

                                    <button
                                        class="btn btn-lg f-14 px-2 py-1 text-dark-grey text-capitalize rounded  dropdown-toggle"
                                        type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                        aria-labelledby="dropdownMenuLink" tabindex="0">
                                        @if($editHousePermission == 'all')
                                                <a class="dropdown-item openRightModal"
                                                data-redirect-url="{{ url()->previous() }}"
                                                href="{{ route('houses.edit', $house->id) }}">@lang('app.edit')</a>
                                        @endif
                                        @if($deleteHousePermission == 'all')
                                                <a class="dropdown-item delete-house">@lang('app.delete')</a>
                                        @endif
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <x-cards.data-row :label="__('houses::modules.house.houseCode')" :value="$house->house_code"
                        html="true" />
                    <x-cards.data-row :label="__('houses::app.menu.typehouse')" :value="$house->typehouse->typehouse_name"
                        html="true" />
                    <x-cards.data-row :label="__('houses::modules.house.housename')" :value="$house->house_name"
                    html="true" />
                    <x-cards.data-row :label="__('houses::app.menu.tower')" :value="$house->tower->tower_name"
                        html="true" />
                    <x-cards.data-row :label="__('houses::app.menu.area')" :value="$house->area->area_name"
                        html="true" />


                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').on('click', '.delete-house', function() {
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
                var url = "{{ route('houses.destroy', $house->id) }}";

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            window.location.href = response.redirectUrl;
                        }
                    }
                });
            }
        });
    });
</script>
