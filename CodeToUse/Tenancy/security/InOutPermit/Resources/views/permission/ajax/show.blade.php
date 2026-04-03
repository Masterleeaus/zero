@php
    $editUnitPermission = user()->permission('edit_trinoutpermit');
    $deleteUnitPermission = user()->permission('delete_trinoutpermit');
@endphp
<!-- ROW START -->
<div class="row mt-4">
    <div class="col-xl-7 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <x-cards.data>
            <div class="row mb-3">
                <div class="col-10">
                    <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-0">
                        @lang('trinoutpermit::app.trinoutpermit.showTrInOutPermit') @lang('app.details')
                    </h4>
                </div>
                <div class="col-2 text-right">
                    <div class="dropdown">
                        <button class="btn btn-lg f-14 px-2 py-1 text-dark-grey text-capitalize rounded  dropdown-toggle"
                            type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                            @if ($editUnitPermission == 'all')
                                <a class="dropdown-item openRightModal" data-redirect-url="{{ url()->previous() }}"
                                    href="{{ route('trinoutpermit.edit', $tenancy->id) }}">@lang('app.edit')</a>
                                <a class="dropdown-item f-14 text-dark"
                                    href="{{ route('trinoutpermit.download', [$tenancy->id]) }}">
                                    @lang('app.download')</a>
                            @endif
                            @if ($deleteUnitPermission == 'all')
                                <a class="dropdown-item delete-unit">@lang('app.delete')</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <x-cards.data-row :label="__('trinoutpermit::app.menu.date')" :value="$tenancy->date" html="true" />
            <x-cards.data-row :label="__('trinoutpermit::app.menu.kartuIdentitas')" :value="strtoupper($tenancy->identity)" html="true" />
            <x-cards.data-row :label="__('trinoutpermit::app.menu.noKartu')" :value="$tenancy->identity_number" html="true" />
            <x-cards.data-row :label="__('trinoutpermit::app.menu.unit')" :value="$tenancy->unit->unit_name" html="true" />
            <x-cards.data-row :label="__('trinoutpermit::app.menu.name')" :value="$tenancy->name" html="true" />
            <x-cards.data-row :label="__('trinoutpermit::app.menu.penanggungJawab')" :value="$tenancy->pj" html="true" />
            <x-cards.data-row :label="__('trinoutpermit::app.menu.noHP')" :value="$tenancy->no_hp" html="true" />
            <x-cards.data-row :label="__('trinoutpermit::app.menu.keterangan')" :value="ucwords(str_replace('-', ' ', $tenancy->keterangan))" html="true" />
            <x-cards.data-row :label="__('trinoutpermit::app.menu.created')" :value="$tenancy->created_at" html="true" />
            <div class="border p-2">
                <x-forms.label fieldId="parent_label" :fieldLabel="__('trinoutpermit::app.menu.notes')" fieldName="parent_label">
                </x-forms.label>
                <p class="mb-0">
                    @foreach ($notes as $index => $note)
                        @if ($index > 0)
                            <br>
                        @endif
                        - {{ $note->remark }}
                    @endforeach
                </p>
            </div>
        </x-cards.data>
    </div>
    <div class="col-xl-5 col-lg-12 col-md-12 ">
        <div class="row">
            <div class="col-md-12">
                <x-cards.data :title="__('trinoutpermit::app.menu.statusAproval')">
                    @if ($tenancy->approved_by)
                        <p class="fs-6 m-0">Approved by {{ $tenancy->approved->name }} at {{ $tenancy->approved_at }}</p>
                    @else
                        Not Yet Approved
                    @endif
                </x-cards.data>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <x-cards.data :title="__('trinoutpermit::app.menu.statusAprovalBm')">
                    @if ($tenancy->approved_by)
                        <p class="fs-6 m-0">Approved BM by {{ $tenancy->approvedBm->name }} at {{ $tenancy->approved_bm_at }}</p>
                    @else
                        Not Yet Approved
                    @endif
                </x-cards.data>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <x-cards.data :title="__('trinoutpermit::app.menu.statusValidasi')">
                    @if ($tenancy->validated_by)
                        <img src="{{ $url }}" heigt="100%" class="mb-2"><br>
                        <div class="border p-2">
                            <x-forms.label fieldId="parent_label" :fieldLabel="__('trinoutpermit::app.menu.remark')" fieldName="parent_label">
                            </x-forms.label>
                            <p class="mb-0">
                                {{ $tenancy->validate_remark }}
                            </p>
                        </div>
                        <p class="fs-6 mt-1 m-0">Validated by {{ $tenancy->validated->name }} at {{ $tenancy->validated_at }}</p>
                    @else
                        Not Yet Validated
                    @endif
                </x-cards.data>
            </div>
        </div>
    </div>
</div>
<!-- ROW END -->

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
                var url = "{{ route('trinoutpermit.destroy', $tenancy->id) }}";

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
