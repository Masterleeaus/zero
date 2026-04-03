<style>
    .customSequence .btn {
        border: none;
    }

    .information-box {
        border-style: dotted;
        margin-bottom: 30px;
        margin-top: 10px;
        padding-top: 10px;
        border-radius: 4px;
    }
</style>
<!-- CREATE INVOICE START -->
<div class="bg-white rounded b-shadow-4 create-inv">
    <div class="px-lg-4 px-md-4 px-3 py-3">
        <h4 class="mb-0 f-21 font-weight-normal text-capitalize">@lang('kontrak::modules.recContract')</h4>
    </div>
    <hr class="m-0 border-top-grey">
    <!-- HEADING -->
    <x-form class="c-inv-form" id="saveInvoiceForm">
        <div class="row p-20">
            <!-- ROW 1 -->
            <div class="col-md-2">
                <div class="form-group">
                    <x-forms.label class="mb-12" fieldId="contract_number" :fieldLabel="__('modules.contracts.contractNumber')" fieldRequired="true">
                    </x-forms.label>
                    <x-forms.input-group>
                        <x-slot name="prepend">
                            <span
                                class="input-group-text">{{ invoice_setting()->contract_prefix }}{{ invoice_setting()->contract_number_separator }}{{ $zero }}</span>
                        </x-slot>
                        <input type="number" name="contract_number" id="contract_number"
                            class="form-control height-35 f-15"
                            value="{{ is_null($lastContract) ? 1 : $lastContract }}">
                    </x-forms.input-group>
                </div>
            </div>
            <div class="col-md-10" style="margin-top: -16px">
                <x-forms.text fieldId="subject" :fieldLabel="__('app.subject')" fieldName="subject" fieldRequired="true">
                </x-forms.text>
            </div>
            <!-- ROW 2 -->
            <div class="col-md-4">
                <div class="form-group c-inv-select">
                    <x-forms.label fieldId="project_id" :fieldLabel="__('app.project')">
                    </x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" data-live-search="true" data-size="8"
                            name="project_id" id="project_id">
                            <option value="">--</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">
                                    {{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <x-forms.label fieldId="contractType" :fieldLabel="__('modules.contracts.contractType')" fieldRequired="true">
                </x-forms.label>
                <x-forms.input-group>
                    <select class="form-control select-picker" name="contract_type" id="contractType"
                        data-live-search="true">
                        <option value="">--</option>
                        @foreach ($contractTypes as $item)
                            <option value="{{ $item->id }}"> {{ $item->name }} </option>
                        @endforeach
                    </select>
                    <x-slot name="append">
                        <button id="createContractType" type="button" class="btn btn-outline-secondary border-grey"
                            data-toggle="tooltip"
                            data-original-title="{{ __('modules.contracts.addContractType') }}">@lang('app.add')</button>
                    </x-slot>
                </x-forms.input-group>
            </div>
            <div class="col-md-4">
                <x-forms.label fieldId="amount" :fieldLabel="__('modules.contracts.contractValue')" :popover="__('modules.contracts.setZero')" fieldRequired="true">
                </x-forms.label>
                <x-forms.input-group>
                    <input type="number" min="0" name="amount" class="form-control height-35 f-14" />
                </x-forms.input-group>
            </div>
            <!-- ROW 2 -->
            <div class="col-md-3">
                <div class="form-group c-inv-select">
                    <x-forms.label fieldId="currency_id" :fieldLabel="__('modules.invoices.currency')">
                    </x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" name="currency_id" id="currency_id">
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}">
                                    {{ $currency->currency_code . ' (' . $currency->currency_symbol . ')' }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <x-forms.label fieldId="billing_date" :fieldLabel="__('Start Date')" fieldRequired="true">
                    </x-forms.label>
                    <div class="input-group">
                        <input type="text" id="start_date" name="start_date"
                            class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                            placeholder="@lang('placeholders.date')"
                            value="{{ now(company()->timezone)->translatedFormat(company()->date_format) }}">
                    </div>
                </div>
            </div>
            <div class="col-md-3 due-date-box" @if ($contract && is_null($contract->end_date)) style="display: none" @endif>
                <div class="form-group">
                    <x-forms.label fieldId="billing_date" :fieldLabel="__('End Date')" fieldRequired="true">
                    </x-forms.label>
                    <div class="input-group">
                        <input type="text" id="end_date" name="end_date"
                            class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                            placeholder="@lang('placeholders.date')"
                            value="{{ now(company()->timezone)->translatedFormat(company()->date_format) }}">
                    </div>
                </div>
            </div>
            <div class="col-md-3 pt-4">
                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :checked="$contract ? is_null($contract->end_date) : ''" :fieldLabel="__('app.withoutDueDate')"
                    fieldName="without_duedate" fieldId="without_duedate" fieldValue="yes" />
            </div>
            <!-- ROW 3 -->
            <div class="col-md-4">
                <div class="form-group c-inv-select mb-4">
                    <x-forms.label fieldId="project_id" :fieldLabel="__('Unit')">
                    </x-forms.label>
                    <div class="select-others height-35 rounded">
                        <select class="form-control select-picker" data-live-search="true" data-size="8"
                            name="unit_id" id="project_id">
                            <option value="">--</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">
                                    {{ $unit->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <x-forms.label fieldId="contractType" :fieldLabel="__('Type Bill')">
                </x-forms.label>
                <x-forms.input-group>
                    <select class="form-control select-picker" name="type_bill" id="contractType"
                        data-live-search="true">
                        <option value="">--</option>
                        @foreach ($contractTypes as $item)
                            <option value="{{ $item->id }}"> {{ $item->name }} </option>
                        @endforeach
                    </select>
                </x-forms.input-group>
            </div>
            <div class="col-md-4">
                <x-forms.label fieldId="rate" :fieldLabel="__('Rate')">
                </x-forms.label>
                <x-forms.input-group>
                    <input type="number" min="0" name="rate" class="form-control height-35 f-14" />
                </x-forms.input-group>
            </div>
            <!-- ROW 4 -->
            <div class="col-md-12">
                <div class="form-group">
                    <x-forms.label fieldId="description" :fieldLabel="__('app.description')">
                    </x-forms.label>
                    <textarea name="contract_detail" id="description-text" class="form-control"></textarea>
                </div>
            </div>
        </div>
        <!-- END SECTION 1 -->

        <div class="px-3 py-3 border-top-grey">
            <h4 class="mb-0 f-21 font-weight-normal text-capitalize">@lang('modules.client.clientDetails')</h4>
        </div>
        <div class="row p-20">
            <div class="col-md-4 mt-3">
                <x-client-selection-dropdown :clients="$clients" />
            </div>
            <div class="col-md-4">
                <x-forms.text fieldId="cell" :fieldLabel="__('modules.client.cell')" :fieldValue="$contract ? $contract->cell : ''" fieldName="cell">
                </x-forms.text>
            </div>
            <div class="col-md-4">
                <x-forms.text fieldId="office" fieldPlaceholder="e.g. +19876543" :fieldValue="$contract ? $contract->office : ''" :fieldLabel="__('modules.client.officePhoneNumber')"
                    fieldName="office">
                </x-forms.text>
            </div>
            <!-- ROW 1 -->
            <div class="col-md-3">
                <x-forms.text fieldId="city" :fieldValue="$contract ? $contract->city : ''" :fieldLabel="__('modules.stripeCustomerAddress.city')" fieldPlaceholder="e.g. Hawthorne"
                    fieldName="city">
                </x-forms.text>
            </div>
            <div class="col-md-3">
                <x-forms.text fieldId="state" :fieldValue="$contract ? $contract->state : ''" :fieldLabel="__('modules.stripeCustomerAddress.state')" fieldName="state"
                    fieldPlaceholder="e.g. California">
                </x-forms.text>
            </div>
            <div class="col-md-3">
                <x-forms.text fieldId="country" :fieldValue="$contract ? $contract->country : ''" :fieldLabel="__('modules.stripeCustomerAddress.country')" fieldName="country">
                </x-forms.text>
            </div>
            <div class="col-md-3">
                <x-forms.text fieldId="postal_code" :fieldValue="$contract ? $contract->postal_code : ''" fieldPlaceholder="e.g. 90250"
                    :fieldLabel="__('modules.stripeCustomerAddress.postalCode')" fieldName="postal_code">
                </x-forms.text>
            </div>
            <!-- ROW 2 -->
            <div class="col-md-6">
                <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.contracts.alternateAddress')" fieldName="alternate_address"
                    fieldId="alternate_address" :fieldPlaceholder="__('placeholders.address')">
                    {{ $contract ? $contract->alternate_address : '' }}
                </x-forms.textarea>
            </div>
            <div class="col-md-6">
                <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.contracts.notes')" fieldName="note" fieldId="note">
                    {{ $contract->description ?? '' }}
                </x-forms.textarea>
            </div>
            <x-forms.custom-field :fields="$fields" class="col-md-12"></x-forms.custom-field>
            <!-- ROW 3 -->
            <!-- END SECTION 2 -->
            <hr class="m-0 border-top-grey">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4 mt-4">
                        <x-forms.label fieldId="rotation" :fieldLabel="__('Schedule Frequency')" fieldRequired="true">
                        </x-forms.label>
                        <div class="form-group c-inv-select">
                            <select class="form-control select-picker" data-live-search="true" data-size="8"
                                name="rotation" id="rotation">
                                <option value="daily">@lang('app.daily')</option>
                                <option value="weekly">@lang('app.weekly')</option>
                                <option value="bi-weekly">@lang('app.bi-weekly')</option>
                                <option value="monthly">@lang('app.monthly')</option>
                                <option value="quarterly">@lang('app.quarterly')</option>
                                <option value="half-yearly">@lang('app.half-yearly')</option>
                                <option value="annually">@lang('app.annually')</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8 mt-4">
                        <div class="form-group">
                            <div class="d-flex">
                                <x-forms.label class="mr-3" fieldId="issue_date" :fieldLabel="__('app.startDate')">
                                </x-forms.label>
                                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2 mt-0" :fieldLabel="__('Immediate start ( Schedule will generate from now )')"
                                    fieldName="immediate_schedule" fieldId="immediate_schedule" fieldValue="true"
                                    fieldRequired="true" />
                            </div>
                            <div class="input-group">
                                <input type="text" id="issue_date" name="issue_date"
                                    class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                    placeholder="@lang('placeholders.date')"
                                    value="{{ Carbon\Carbon::now(company()->timezone)->format(company()->date_format) }}">
                            </div>
                            <small class="form-text text-muted">@lang('Date from which schedule will be created')</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2 mt-0" :fieldLabel="__('Total Count')" fieldName="billing_cycle"
                            fieldId="billing_cycle" :fieldHelp="__('No. of schedule cycles to be charged (set -1 for infinite cycles)')" fieldRequired="true" />
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-4 information-box">
                <p id="plan">@lang('Schedule will be generate Daily')</p>
                <p id="current_date">@lang('First Schedule will be generated on')
                    {{ Carbon\Carbon::now()->translatedFormat(company()->date_format) }}</p>
                <p id="next_date">@lang('Next Schedule Date will be')
                    {{ Carbon\Carbon::now()->addDay()->translatedFormat(company()->date_format) }}</p>
                <p>@lang('And so on....')</p>
                <span id="billing"></span>
            </div>
        </div>

        <!-- CANCEL SAVE START -->
        <x-form-actions class="c-inv-btns d-block d-lg-flex d-md-flex">
            <x-forms.button-primary data-type="save" class="save-form mr-3" icon="check">@lang('app.save')
                Recurring
            </x-forms.button-primary>

            <x-forms.button-cancel :link="route('work.index')" class="border-0 ">@lang('app.cancel')
            </x-forms.button-cancel>
        </x-form-actions>
        <!-- CANCEL SAVE END -->

    </x-form>
    <!-- FORM END -->
</div>
<script>
    $(document).ready(function() {
        const dp1 = datepicker('#start_date', {
            position: 'bl',
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
            onSelect: (instance, date) => {
                dp1.setMax(date);
            },
            ...datepickerConfig
        });

        const dp3 = datepicker('#issue_date', {
            position: 'bl',
            onSelect: (instance, date) => {
                var rotation = $('#rotation').val();
                nextDate(rotation, date);
            },
            ...datepickerConfig
        });

        function ucWord(str) {
            str = str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                return letter.toUpperCase();
            });
            return str;
        }
        
        $('#without_duedate').click(function () {
            $('.due-date-box').toggle();
        });

        $('.save-form').click(function() {
            $.easyAjax({
                url: "{{ route('kontrak.store') }}",
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                redirect: true,
                data: $('#saveInvoiceForm').serialize(),
            });
        });

        init(RIGHT_MODAL);
    });

    $('body').on('change keyup', '#rotation, #billing_cycle', function() {
        var billingCycle = $('#billing_cycle').val();
        billingCycle != '' ? $('#billing').html("{{ __('No. of billing cycle is') }}" + ' ' + billingCycle) :
            $(
                '#billing').html('');

        var rotation = $('#rotation').val();
        switch (rotation) {
            case 'daily':
                var rotationType = "{{ __('app.daily') }}";
                break;
            case 'weekly':
                var rotationType = "{{ __('app.every') }}" + ' ' + "{{ __('Week') }}";
                break;
            case 'bi-weekly':
                var rotationType = "{{ __('app.every') }}" + ' ' + "{{ __('Bi-Week') }}";
                break;
            case 'monthly':
                var rotationType = "{{ __('app.every') }}" + ' ' + "{{ __('Month') }}";
                break;
            case 'quarterly':
                var rotationType = "{{ __('app.every') }}" + ' ' + "{{ __('Quarter') }}";
                break;
            case 'half-yearly':
                var rotationType = "{{ __('app.every') }}" + ' ' + "{{ __('Half Year') }}";
                break;
            case 'annually':
                var rotationType = "{{ __('app.every') }}" + ' ' + "{{ __('Year') }}";
                break;
            default:
        }

        $('#plan').html("{{ __('Schedule will be generate') }}" + ' ' + rotationType);
        if ($('#immediate_schedule').is(':checked')) {
            var date = moment().toDate();
        } else {
            var startDate = $('#issue_date').val();
            var date = moment(startDate, 'DD-MM-YYYY').toDate();
        }

        nextDate(rotation, date);
    })

    $('#immediate_schedule').change(function() {
        var rotation = $('#rotation').val();
        if ($(this).is(':checked')) {
            var date = moment().toDate();
            $('#issue_date').val(moment(date, "DD-MM-YYYY").format('{{ company()->moment_date_format }}'));
            $('#issue_date').prop('disabled', true)
        } else {
            $('#issue_date').prop('disabled', false)
            var startDate = $('#issue_date').val();
            var date = moment(startDate, 'DD-MM-YYYY').toDate();
        }
        nextDate(rotation, date);
    })

    function nextDate(rotation, date) {
        var nextDate = moment(date, "DD-MM-YYYY");
        var currentValue = nextDate.format('{{ company()->moment_date_format }}');

        switch (rotation) {
            case 'daily':
                var rotationDate = nextDate.add(1, 'days');
                break;
            case 'weekly':
                var rotationDate = nextDate.add(1, 'weeks');
                break;
            case 'bi-weekly':
                var rotationDate = nextDate.add(2, 'weeks');
                break;
            case 'monthly':
                var rotationDate = nextDate.add(1, 'months');
                break;
            case 'quarterly':
                var rotationDate = nextDate.add(1, 'quarters');
                break;
            case 'half-yearly':
                var rotationDate = nextDate.add(2, 'quarters');
                break;
            case 'annually':
                var rotationDate = nextDate.add(1, 'years');
                break;
            default:
        }
        var value = rotationDate.format('{{ company()->moment_date_format }}');

        $('#current_date').html("{{ __('First Schedule will be generated on') }}" + ' ' + currentValue);
        $('#next_date').html("{{ __('Next Schedule Date will be') }}" + ' ' + value);
    }
</script>
