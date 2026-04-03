@php
$manageContractTypePermission = user()->permission('manage_contract_type');
$addClientPermission = user()->permission('add_clients');
@endphp

{{-- @if (!in_array('customers', user_modules())) --}}
@if (!in_array('customers', user_modules()) && !in_array('customer', user_roles()))
    <x-alert class="mb-3" type="danger" icon="exclamation-circle"><span>@lang('team chat.enableClientModule')</span>
        <x-forms.link-secondary icon="arrow-left" :link="route('service agreements.show', $service agreement->id)">@lang('app.back')</x-forms.link-secondary>
    </x-alert>
@else

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-service agreement-data-form">
            @method('PUT')
            <div class="add-customer bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.contractDetails')</h4>
                <div class="row p-20">
                                    <!-- CONTRACT NUMBER START -->
                <div class="col-md-2">
                    <div class="form-group mb-lg-0 mb-md-0 mb-4">
                        <x-forms.label class="mb-12" fieldId="contract_number"
                            :fieldLabel="__('modules.service agreements.contractNumber')" fieldRequired="true">
                        </x-forms.label>
                        <input type="text" name="contract_number" id="contract_number" class="form-control height-35 f-15 readonly-background" readonly
                                value="{{ $service agreement->contract_number }}">
                    </div>
                </div>
                <!-- CONTRACT NUMBER END -->
                    <div class="col-md-10" style="margin-top: -16px">
                        <x-forms.text fieldId="subject" :fieldLabel="__('app.subject')" fieldName="subject"
                            :fieldValue="$service agreement->subject" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group c-inv-select mb-4">
                            <x-forms.label fieldId="project_id" :fieldLabel="__('app.site')">
                            </x-forms.label>
                            <div class="select-others height-35 rounded">
                                <select class="form-control select-picker" data-live-search="true" data-size="8"
                                    name="project_id" id="project_id">
                                    <option value="">--</option>
                                        @foreach ($sites as $site)
                                            <option  @if ($site->id == $service agreement->project_id) selected @endif value="{{ $site->id }}">
                                                {{ $site->project_name }}</option>
                                        @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="description" :fieldLabel="__('app.description')">
                            </x-forms.label>
                            <div id="description">{!! $service agreement->contract_detail !!}</div>
                            <textarea name="description" id="description-text" class="d-none"></textarea>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.datepicker fieldId="start_date" fieldRequired="true"
                            :fieldLabel="__('modules.sites.startDate')" fieldName="start_date"
                            :fieldValue="$service agreement->start_date->timezone(company()->timezone)->format(company()->date_format)"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.datepicker fieldId="end_date"
                            :fieldValue="$service agreement->end_date==null ? $service agreement->end_date : $service agreement->end_date->timezone(company()->timezone)->format(company()->date_format)"
                            :fieldLabel="__('modules.timeLogs.endDate')" fieldName="end_date"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.label class="mt-3" fieldId="contractType"
                            :fieldLabel="__('modules.service agreements.contractType')" fieldRequired="true"></x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="contract_type" id="contractType"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($contractTypes as $item)
                                    <option @if ($item->id == $service agreement->contract_type_id) selected @endif value="{{ $item->id }}">
                                        {{ $item->name }}</option>
                                @endforeach
                            </select>

                            @if ($manageContractTypePermission == 'all')
                                <x-slot name="append">
                                    <button id="createContractType" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{ __('modules.service agreements.addContractType') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.label class="mt-3" fieldId="amount"
                            :fieldLabel="__('modules.service agreements.contractValue')"
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

                    <!-- CURRENCY START -->
                    <div class="col-md-6 col-lg-3">
                        <div class="form-group c-inv-select mb-lg-0 mb-md-0 mb-4">
                            <x-forms.label fieldId="currency_id" :fieldLabel="__('modules.invoices.currency')">
                            </x-forms.label>

                            <div class="select-others height-35 rounded">
                                <select class="form-control select-picker" name="currency_id" id="currency_id">
                                    @foreach ($currencies as $currency)
                                    <option
                                        @if ($currency->id == $service agreement->currency_id) selected @endif
                                        value="{{ $currency->id }}">
                                        {{ $currency->currency_code . ' (' . $currency->currency_symbol . ')' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- CURRENCY END -->

                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-top-grey">
                    @lang('modules.customer.clientDetails')</h4>
                <div class="row p-20">
                    <div class="col-md-6 col-lg-4">
                        <x-customer-selection-dropdown :customers="$customers" :selected="$service agreement->client_id" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.text fieldId="cell" :fieldLabel="__('modules.customer.cell')"
                            :fieldValue="$service agreement->cell" fieldName="cell">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.text fieldId="office" :fieldValue="$service agreement->office"
                            :fieldLabel="__('modules.customer.officePhoneNumber')" fieldName="office">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.text fieldId="city" :fieldValue="$service agreement->city"
                            :fieldLabel="__('modules.stripeCustomerAddress.city')" fieldName="city">
                        </x-forms.text>
                    </div>


                    <div class="col-md-6 col-lg-3">
                        <x-forms.text fieldId="state" :fieldValue="$service agreement->state"
                            :fieldLabel="__('modules.stripeCustomerAddress.state')" fieldName="state">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.text fieldId="country" :fieldValue="$service agreement->country"
                            :fieldLabel="__('modules.stripeCustomerAddress.country')" fieldName="country">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.text fieldId="postal_code" :fieldValue="$service agreement->postal_code"
                            :fieldLabel="__('modules.stripeCustomerAddress.postalCode')" fieldName="postal_code">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                            :fieldLabel="__('modules.service agreements.alternateAddress')" fieldName="alternate_address"
                            fieldId="alternate_address" :fieldPlaceholder="__('placeholders.address')"
                            :fieldValue="$service agreement->alternate_address">
                        </x-forms.textarea>
                    </div>
                    <div class="col-md-6">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.service agreements.notes')"
                            fieldName="note" fieldId="note" :fieldValue="$service agreement->contract_note">
                        </x-forms.textarea>
                    </div>

                    <x-forms.custom-field :fields="$fields" :model="$service agreement" class="col-md-12"></x-forms.custom-field>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-service agreement-form" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('service agreements.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

@endif
<script>
    $(document).ready(function() {

        const dp1 = datepicker('#start_date', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $service agreement->start_date) }}"),
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
            dateSelected: new Date("{{ $service agreement->end_date ? str_replace('-', '/', $service agreement->end_date) : str_replace('-', '/', now()) }}"),
            onSelect: (instance, date) => {
                dp1.setMax(date);
            },
            ...datepickerConfig
        });

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        $('#add-customer').click(function() {
            $(MODAL_XL).modal('show');

            const url = "{{ route('customers.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function(response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        $('#save-service agreement-form').click(function() {
            var note = document.getElementById('description').children[0].innerHTML;
            document.getElementById('description-text').value = note;

            const url = "{{ route('service agreements.update', $service agreement->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-service agreement-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-service agreement-form",
                file: true,
                data: $('#save-service agreement-data-form').serialize(),
                redirect: true
            })
        });

        quillMention(null, '#description');

        $('#createContractType').click(function() {
            const url = "{{ route('contractTypes.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });
        $('#client_list_id').change(function() {
            var id = $(this).val();
            if (id == '') {
                id = 0;
            }
            var url = "{{ route('service agreements.project_detail', ':id') }}";
            url = url.replace(':id', id);
            var token = "{{ csrf_token() }}";

            $.easyAjax({
                url: url,
                container: '#save-service agreement-data-form',
                type: "POST",
                blockUI: true,
                data: {
                    _token: token
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#project_id').html(response.data);
                        $('#project_id').selectpicker('refresh');
                    }
                }
            });
        });
        $('#save-service agreement-data-form').on('change', '#project_id', function () {
            let id = $(this).val();
            if (id === '' || id == null) {
                id = 0;
            }
            let url = "{{ route('customers.client_details', ':id') }}";
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                type: "GET",
                container: '#save-service agreement-data-form',
                blockUI: true,
                redirect: true,
                success: function (data) {
                        $('#client_list_id').html(data.teamData);
                        $('#client_list_id').selectpicker('refresh');
                }
            })
        });
        <x-forms.custom-field-filejs/>

        init(RIGHT_MODAL);
    });

</script>
