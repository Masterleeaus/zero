@php
$addContractPermission = user()->permission('renew_contract');
@endphp

<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">

    <x-cards.data :title="__('modules.service agreements.contractRenewalHistory')">
        @if ($addContractPermission == 'all' || ($addContractPermission == 'added' && $service agreement->added_by == user()->id))

            <div class="row">
                <div class="col-md-12">
                    <a class="f-15 f-w-500" href="javascript:;" id="renew-service agreement"><i
                            class="icons icon-refresh font-weight-bold mr-1"></i>
                        @lang('modules.service agreements.renewContract')</a>
                </div>
            </div>

            <x-form id="save-renew-data-form" class="d-none">
                <input type="hidden" name="contract_id" value="{{ $service agreement->id }}">

                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <x-forms.datepicker fieldId="start_date" fieldRequired="true"
                            :fieldLabel="__('modules.sites.startDate')" fieldName="start_date"
                            :fieldValue="(!is_null($service agreement->end_date) ? $service agreement->end_date->timezone(company()->timezone)->format(company()->date_format) : $service agreement->start_date->timezone(company()->timezone)->translatedFormat(company()->date_format))"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.datepicker fieldId="end_date"
                            :fieldValue="($service agreement ? ($service agreement->end_date==null ? $service agreement->end_date : $service agreement->end_date->timezone(company()->timezone)->format(company()->date_format)) : '')"
                            :fieldLabel="__('modules.timeLogs.endDate')" fieldName="end_date"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.label class="mt-3" fieldId="amount" :fieldLabel="__('modules.service agreements.contractValue')"
                            :popover="__('modules.service agreements.setZero')" fieldRequired="true"></x-forms.label>
                        <x-forms.input-group>
                            <x-slot name="append">
                                <span
                                    class="input-group-text height-35 border bg-white">{{ $service agreement->currency->currency_code }}</span>
                            </x-slot>

                            <input type="number" min="0" name="amount" value="{{ $service agreement->amount ?? '' }}"
                                class="form-control height-35 f-14" />
                        </x-forms.input-group>
                    </div>
                </div>
                <div class="w-100 justify-content-end d-flex mt-2">
                    <x-forms.button-cancel link="javascript:;" id="cancel-renew" class="border-0 mr-3">@lang('app.cancel')
                    </x-forms.button-cancel>
                    <x-forms.button-primary id="submit-renew" icon="check">@lang('app.renew')
                        </x-forms.button-primary>
                </div>
            </x-form>
        @endif


        <div class="d-flex flex-wrap justify-content-between mt-4" id="comment-list">
            @include('service agreements.renew.renew_history')
        </div>

    </x-cards.data>
</div>
<!-- TAB CONTENT END -->

<script>
    $('#renew-service agreement').click(function() {
        $(this).closest('.row').addClass('d-none');
        $('#save-renew-data-form').removeClass('d-none');
    });

    $('#cancel-renew').click(function() {
        $('#save-renew-data-form').addClass('d-none');
        $('#renew-service agreement').closest('.row').removeClass('d-none');
    });

    $(document).ready(function() {

        const dp1 = datepicker('#start_date', {
            position: 'bl',
            dateSelected: new Date("{{ $service agreement->end_date ? str_replace('-', '/', $service agreement->end_date) : str_replace('-', '/', $service agreement->start_date) }}"),
            minDate: new Date("{{ $service agreement->end_date ? str_replace('-', '/', $service agreement->end_date) : str_replace('-', '/', $service agreement->start_date) }}"),
            onSelect: (instance, date) => {
                if (typeof dp2.dateSelected !== 'undefined' && dp2.dateSelected.getTime() < date
                    .getTime()) {
                    dp2.setDate(date, true)
                }
                if (typeof dp2.dateSelected === 'undefined') {
                    dp2.setDate(date, true)
                }
                dp2.setMin(date);
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#end_date', {
            position: 'bl',
            dateSelected: new Date("{{ $service agreement->end_date ? str_replace('-', '/', $service agreement->end_date->addDays($service agreement->end_date->diffInDays($service agreement->start_date))) : str_replace('-', '/', now()) }}"),
            minDate: new Date("{{ $service agreement->end_date ? str_replace('-', '/', $service agreement->end_date) : str_replace('-', '/', $service agreement->start_date) }}"),
            onSelect: (instance, date) => {
                dp1.setMax(date);
            },
            ...datepickerConfig
        });

        $('#submit-renew').click(function() {
            const url = "{{ route('service agreement-renew.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-renew-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#submit-renew",
                data: $('#save-renew-data-form').serialize(),
                success: function(response) {
                    if (response.status == "success") {
                        $('#comment-list').html(response.view);
                    }

                }
            });
        });

        $('body').on('click', '.delete-comment', function() {
            var id = $(this).data('row-id');
            Swal.fire({
                title: "@lang('team chat.sweetAlertTitle')",
                text: "@lang('team chat.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('team chat.confirmDelete')",
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
                    var url = "{{ route('service agreement-renew.destroy', ':id') }}";
                    url = url.replace(':id', id);

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
                                $('#comment-list').html(response.view);
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.edit-comment', function() {
            var id = $(this).data('row-id');
            var url = "{{ route('service agreement-renew.edit', ':id') }}";
            url = url.replace(':id', id);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

    });

</script>
