@php
    $editUnitPermission = user()->permission('edit_tenan');
    $deleteUnitPermission = user()->permission('delete_tenan');
@endphp
<div class="card border-0 invoice">
    <div class="card-body">
        <div class="invoice-table-wrapper">
            <table width="100%">
                <tr class="inv-logo-heading">
                    <td class="font-weight-bold f-21 text-dark text-uppercase mt-4 mt-lg-0 mt-md-0" align="center">
                        @lang('traccesscard::app.card.showCard')</td>
                </tr>
                <tr class="inv-num">
                    <td>
                        <table class="inv-num-date text-dark f-13 mt-3">
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">@lang('traccesscard::app.menu.date')</td>
                                <td class="border-left-0">{{ ucwords($card->date) }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">@lang('traccesscard::app.menu.unit')</td>
                                <td class="border-left-0">{{ ucwords($card->unit->unit_name) }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">@lang('traccesscard::app.menu.resident')</td>
                                <td class="border-left-0">{{ ucwords($card->name) }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">@lang('traccesscard::app.menu.noHP')</td>
                                <td class="border-left-0">{{ ucwords($card->no_hp) }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">@lang('traccesscard::app.menu.biaya')</td>
                                <td class="border-left-0">{{ ucwords($card->fee) }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light-grey border-right-0 f-w-500">@lang('traccesscard::app.menu.created')</td>
                                <td class="border-left-0">{{ $card->created_at }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td height="20"></td>
                </tr>
            </table>
            <table width="100%" class="inv-desc d-none d-lg-table d-md-table">
                <tr>
                    <td colspan="2">
                        <table class="inv-detail f-14 table-responsive-sm" width="100%">
                            <tr class="text-dark-grey font-weight-bold f-14">
                                <td width="25%">
                                    @lang('traccesscard::app.menu.name')</td>
                                <td width="25%">
                                    @lang('traccesscard::app.menu.status')</td>
                                <td width="45%">
                                    @lang('traccesscard::app.menu.noKartu')</td>
                                <td width="5%" class="border-0"> </td>
                            </tr>
                            @foreach ($card->items as $item)
                                <tr class="text-dark font-weight-semibold f-13 border">
                                    <td>{{ ucwords($item->name) }}</td>
                                    <td>{{ ucwords($item->status) }}</td>
                                    <td>{{ $item->no_kartu }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
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
                        <a class="dropdown-item f-14 text-dark delete-unit" href="javascript:;"
                            data-invoice-id="{{ $card->id }}">
                            <i class="fa fa-trash f-w-500 mr-2 f-11"></i> @lang('app.delete')
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item f-14 text-dark" href="{{ route('card.download', [$card->id]) }}">
                            <i class="fa fa-download f-w-500 mr-2 f-11"></i> @lang('app.download')
                        </a>
                    </li>
                </ul>
            </div>
            @if (in_array('client', user_roles()))
                <x-forms.button-cancel :link="route('card.client')">@lang('app.cancel')
                </x-forms.button-cancel>
            @else
                <x-forms.button-cancel :link="route('card.index')">@lang('app.cancel')
                </x-forms.button-cancel>
            @endif
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
                var url = "{{ route('card.destroy', $card->id) }}";

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
