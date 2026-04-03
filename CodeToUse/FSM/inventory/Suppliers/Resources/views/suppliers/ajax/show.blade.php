@php
    $editUnitPermission = user()->permission('edit_unit');
    $deleteUnitPermission = user()->permission('delete_unit');
@endphp
<div id="leave-detail-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">@lang('suppliers::app.menu.supplier') @lang('app.details')</h3>
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
                                    @if ($editUnitPermission == 'all')
                                        <a class="dropdown-item openRightModal"
                                            data-redirect-url="{{ url()->previous() }}"
                                            href="{{ route('suppliers.edit', $unit->id) }}">@lang('app.edit')</a>
                                    @endif
                                    @if ($deleteUnitPermission == 'all')
                                        <a class="dropdown-item delete-unit">@lang('app.delete')</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <x-cards.data-row :label="__('app.name')" :value="$unit->name" html="true" />
                    <x-cards.data-row :label="__('app.phone')" :value="$unit->phone" html="true" />
                    <x-cards.data-row :label="__('suppliers::app.menu.fax')" :value="$unit->fax" html="true" />
                    <x-cards.data-row :label="__('suppliers::app.menu.kodePos')" :value="$unit->kode_pos" html="true" />
                    <x-cards.data-row :label="__('suppliers::app.menu.alamat')" :value="$unit->alamat" html="true" />
                    <x-cards.data-row :label="__('suppliers::app.menu.contactPerson')" :value="$unit->contact_person" html="true" />
                    <x-cards.data-row :label="__('suppliers::app.menu.phoneCP')" :value="$unit->phone_contact_person" html="true" />
                    <x-cards.data-row :label="__('app.email')" :value="$unit->email" html="true" />
                </div>
            </div>
        </div>
    </div>
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
                var url = "{{ route('suppliers.destroy', $unit->id) }}";

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
