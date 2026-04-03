<div class="card border-0 invoice">
    <!-- CARD BODY START -->
    <div class="card-body">
        <div class="invoice-table-wrapper">
            <table width="100%">
                <tr class="inv-logo-heading">
                    <td class="font-weight-bold f-21 text-dark text-uppercase mt-4 mt-lg-0 mt-md-0">
                        @lang('units::app.menu.unit'): {{ $user->name }}
                    </td>
                </tr>
                <tr>
                    <td height="20"></td>
                </tr>
            </table>
            <div class="row">
                <div class="col-md-12">
                    <table width="100%" class="inv-desc d-none d-lg-table d-md-table">
                        <td colspan="2" class="mt-4">
                            <table class="inv-detail f-14 table-responsive-sm mt-4" width="100%">
                                <tr class="i-d-heading text-dark-grey font-weight-bold">
                                    <td class="border bg-light-grey">@lang('units::modules.unit.unitCode')</td>
                                    <td class="border bg-light-grey">@lang('units::modules.unit.unitName')</td>
                                    <td class="border bg-light-grey">@lang('units::modules.unit.unitFloor')</td>
                                    <td class="border bg-light-grey">@lang('units::modules.unit.unitTower')</td>
                                    <td class="border bg-light-grey">@lang('units::modules.unit.unitTypeUnit')</td>
                                    <td class="border bg-light-grey">@lang('units::modules.unit.unitLuas')</td>
                                    <td class="border bg-light-grey">@lang('app.action')</td>
                                </tr>
                                @foreach ($units as $item)
                                    <tr class="text-dark font-weight-semibold f-13 border">
                                        <td>{{ $item->unit->unit_code }}</td>
                                        <td>{{ $item->unit->unit_name }}</td>
                                        <td>{{ $item->unit->floor->floor_name ?? '--' }}</td>
                                        <td>{{ $item->unit->tower->tower_name ?? '--' }}</td>
                                        <td>{{ $item->unit->typeunit->typeunit_name ?? '--' }}</td>
                                        <td>{{ $item->unit->luas ?? '--' }}</td>
                                        <td>
                                            <a class="btn btn-sm btn-primary" href="{{ route('units.show', [$item->unit->id]) }}">
                                                <i class="fa fa-eye mr-2"></i>
                                                @lang('app.view')
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- CARD BODY END -->
    <!-- CARD FOOTER START -->
    <div class="card-footer bg-white border-0 d-flex justify-content-start py-0 py-lg-4 py-md-4 mb-4 mb-lg-3 mb-md-3 ">
        <div class="d-flex">
            <div class="inv-action mr-3 mr-lg-3 mr-md-3 dropup">
                <button class="dropdown-toggle btn-primary" type="button" id="dropdownMenuButton"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@lang('app.action')
                    <span><i class="fa fa-chevron-up f-15"></i></span>
                </button>
                <!-- DROPDOWN - INFORMATION -->
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton" tabindex="0">
                    <li>
                        <a class="dropdown-item f-14 text-dark"
                            href="{{ route('units-configuration.edit', [$user->id]) }}">
                            <i class="fa fa-edit f-w-500 mr-2 f-11"></i> @lang('app.edit')
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item f-14 text-dark delete-unit" href="javascript:;">
                            <i class="fa fa-trash f-w-500 mr-2 f-11"></i> @lang('app.delete')
                        </a>
                    </li>
                </ul>
            </div>
            <x-forms.button-cancel :link="route('units-configuration.index')" class="border-0 mr-3">@lang('app.cancel')
            </x-forms.button-cancel>
        </div>
    </div>
    <!-- CARD FOOTER END -->
</div>

<script>
    $('body').on('click', '.delete-unit', function() {
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
                var url = "{{ route('units-configuration.destroy', $user->id) }}";
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
