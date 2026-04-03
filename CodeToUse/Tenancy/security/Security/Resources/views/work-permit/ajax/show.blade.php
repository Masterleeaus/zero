@php
    $editUnitPermission = user()->permission('edit_security');
    $deleteUnitPermission = user()->permission('delete_security');
@endphp
<!-- ROW START -->
<div class="row mt-4">
    <div class="col-xl-7 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <x-cards.data>
            <div class="row mb-3">
                <div class="col-10">
                    <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-0">
                        @lang('security::app.menu.showTransfer') @lang('app.details')
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
                                    href="{{ route('work-permits.edit', $wp->id) }}">@lang('app.edit')</a>
                                <a class="dropdown-item f-14 text-dark"
                                    href="{{ route('work-permits.download', [$wp->id]) }}">
                                    @lang('app.download')</a>
                            @endif
                            @if ($deleteUnitPermission == 'all')
                                <a class="dropdown-item delete-unit">@lang('app.delete')</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <x-cards.data-row :label="__('security::app.menu.date')" :value="$wp->date" html="true" />
            <x-cards.data-row :label="__('security::app.menu.companyName')" :value="$wp->company_name" html="true" />
            <x-cards.data-row :label="__('security::app.menu.companyAddress')" :value="$wp->company_address" html="true" />
            <x-cards.data-row :label="__('security::app.menu.projectManj')" :value="$wp->project_manj" html="true" />
            <x-cards.data-row :label="__('security::app.menu.siteCoor')" :value="$wp->site_coor" html="true" />
            <x-cards.data-row :label="__('security::app.menu.jenisPekerjaan')" :value="ucwords($wp->jenis_pekerjaan)" html="true" />
            <x-cards.data-row :label="__('security::app.menu.lingkupPekerjaan')" :value="ucwords(str_replace('-', ' ', $wp->lingkup_pekerjaan))" html="true" />
            <x-cards.data-row :label="__('security::app.menu.unit')" :value="$wp->unit->unit_name" html="true" />
            <x-cards.data-row :label="__('security::app.menu.noHP')" :value="$wp->phone" html="true" />
            <x-cards.data-row :label="__('security::app.menu.dateStart')" :value="$wp->date_start" html="true" />
            <x-cards.data-row :label="__('security::app.menu.dateEnd')" :value="$wp->date_end" html="true" />
            <x-cards.data-row :label="__('security::app.menu.detailPekerjaan')" :value="ucwords($wp->detail_pekerjaan)" html="true" />
            <x-cards.data-row :label="__('security::app.menu.keterangan')" :value="ucwords(str_replace('-', ' ', $wp->keterangan))" html="true" />
            <x-cards.data-row :label="__('security::app.menu.created')" :value="$wp->created_at" html="true" />
            <div class="border p-2">
                <x-forms.label fieldId="parent_label" :fieldLabel="__('security::app.menu.notes')" fieldName="parent_label">
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
                <x-cards.data :title="__('security::app.menu.statusAproval')">
                    @if ($wp->approved_by)
                        <p class="fs-6 m-0">Approved by {{ $wp->approved->name }} at {{ $wp->approved_at }}</p>
                    @else
                        Not Yet Approved
                    @endif
                </x-cards.data>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <x-cards.data :title="__('security::app.menu.statusValidasi')">
                    @if ($wp->validated_by)
                        <img src="{{ $url }}" heigt="100%" class="mb-2"><br>
                        <div class="border p-2">
                            <x-forms.label fieldId="parent_label" :fieldLabel="__('security::app.menu.remark')" fieldName="parent_label">
                            </x-forms.label>
                            <p class="mb-0">
                                {{ $wp->validate_remark }}
                            </p>
                        </div>
                        <p class="fs-6 mt-1 m-0">Validated by {{ $wp->validated->name }} at {{ $wp->validated_at }}</p>
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
                var url = "{{ route('work-permits.destroy', $wp->id) }}";

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
