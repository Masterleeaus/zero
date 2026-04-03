@extends('layouts.app')

@push('styles')
    <style>
        .customSequence .btn {
            border: none;
        }

        .billingInterval .form-group {
            margin-top: 0px !important;
        }

        .information-box {
            border-style: dotted;
            margin-bottom: 30px;
            margin-top: 10px;
            padding-top: 10px;
            border-radius: 4px;
        }
    </style>
@endpush

@section('content')
    @php
        $billingCycle = $schedule->unlimited_recurring == 1 ? -1 : $schedule->billing_cycle;
        $recurringSchedule = count($schedule->recurrings) > 0 ? 'disabled' : '';
    @endphp
    <!-- CREATE INVOICE START -->
    <div class="content-wrapper">
        <div class="bg-white rounded b-shadow-4 create-inv">
            <div class="px-lg-4 px-md-4 px-3 py-3">
                <h4 class="mb-0 f-21 font-weight-normal text-capitalize">@lang('kontrak::modules.recContract')</h4>
            </div>
            <hr class="m-0 border-top-grey">
            <!-- FORM START -->
            <x-form class="c-inv-form" id="saveInvoiceForm">@method('PUT')
                <div class="row px-lg-4 px-md-4 px-3 py-3">
                    <div class="col-md-4">
                        <div class="form-group c-inv-select mb-4">
                            <x-forms.label fieldId="project_id" :fieldLabel="__('kontrak::app.menu.unit')">
                            </x-forms.label>
                            <div class="select-others height-35 rounded">
                                <select class="form-control select-picker" data-live-search="true" data-size="8"
                                    name="unit_id" id="project_id">
                                    <option value="">--</option>
                                    @foreach ($units as $unit)
                                        <option @if ($unit->id == $schedule->unit_id) selected @endif value="{{ $unit->id }}">
                                            {{ $unit->unit_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <x-forms.label fieldId="contractType" :fieldLabel="__('kontrak::app.menu.typeBill')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="type_bill" id="contractType"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($contractTypes as $item)
                                    <option  @if ($item->id == $schedule->type_bill) selected @endif value="{{ $item->id }}"> {{ $item->name }} </option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                    <div class="col-md-4">
                        <x-forms.label fieldId="rate" :fieldLabel="__('kontrak::app.menu.rate')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <input type="number" min="0" name="rate" class="form-control height-35 f-14" value="{{ $schedule->rate }}" />
                        </x-forms.input-group>
                    </div>
                    <hr class="m-0 border-top-grey">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-4 mt-4">
                                <x-forms.label fieldId="rotation" :fieldLabel="__('Schedule Frequency')" fieldRequired="true">
                                </x-forms.label>
                                <div class="form-group c-inv-select">
                                    <select class="form-control select-picker" data-live-search="true" data-size="8"
                                        name="rotation" id="rotation">
                                        <option @if ($schedule->rotation == 'daily') selected @endif value="daily">
                                            @lang('app.daily')</option>
                                        <option @if ($schedule->rotation == 'weekly') selected @endif value="weekly">
                                            @lang('app.weekly')</option>
                                        <option @if ($schedule->rotation == 'bi-weekly') selected @endif value="bi-weekly">
                                            @lang('app.bi-weekly')</option>
                                        <option @if ($schedule->rotation == 'monthly') selected @endif value="monthly">
                                            @lang('app.monthly')</option>
                                        <option @if ($schedule->rotation == 'quarterly') selected @endif value="quarterly">
                                            @lang('app.quarterly')</option>
                                        <option @if ($schedule->rotation == 'half-yearly') selected @endif value="half-yearly">
                                            @lang('app.half-yearly')</option>
                                        <option @if ($schedule->rotation == 'annually') selected @endif value="annually">
                                            @lang('app.annually')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8 mt-4">
                                <div class="form-group">
                                    <div class="d-flex">
                                        <x-forms.label class="mr-3" fieldId="issue_date" :fieldLabel="__('app.startDate')">
                                        </x-forms.label>
                                    </div>
                                    <div class="input-group">
                                        <input type="text" id="issue_date" name="issue_date"
                                            class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                            value="{{ $schedule->issue_date->translatedFormat(company()->date_format) }}"
                                            readonly>
                                    </div>
                                    <small class="form-text text-muted">@lang('Date from which schedule will be created')</small>
                                </div>
                            </div>
                            <div class="col-lg-4 mt-0">
                                <x-forms.number class="mr-0  mr-lg-2 mr-md-2 mt-0" :fieldLabel="__('Total Count')"
                                    fieldName="billing_cycle" fieldId="billing_cycle" :fieldValue="$billingCycle" :fieldHelp="__('No. of schedule cycles to be charged (set -1 for infinite cycles)')"
                                    :fieldReadOnly="count($schedule->recurrings) > 0 ? true : ''" />
                            </div>
                        </div>
                    </div>
                    @php
                        switch ($schedule->rotation) {
                            case 'daily':
                                $rotationType = __('app.daily');
                                break;
                            case 'weekly':
                                $rotationType = __('modules.recurringSchedule.week');
                                break;
                            case 'bi-weekly':
                                $rotationType = __('app.bi-week');
                                break;
                            case 'monthly':
                                $rotationType = __('app.month');
                                break;
                            case 'quarterly':
                                $rotationType = __('app.quarter');
                                break;
                            case 'half-yearly':
                                $rotationType = __('app.half-year');
                                break;
                            case 'annually':
                                $rotationType = __('app.year');
                                break;
                            default:
                        }
                    @endphp

                    <div class="col-md-4 mt-4 information-box">
                        <p id="plan">@lang('Schedule will be generate') @if ($schedule->rotation != 'daily')
                                @lang('app.every')
                            @endif {{ $rotationType }}</p>
                        @if (count($schedule->recurrings) == 0)
                            <p id="current_date">@lang('modules.recurringSchedule.currentScheduleDate')
                                {{ $schedule->issue_date->translatedFormat(company()->date_format) }}</p>
                        @endif
                        <p id="next_date"></p>
                        @if (count($schedule->recurrings) == 0)
                            <p>@lang('Next Schedule Date will be ')</p>
                        @endif
                        <p id="billing">@lang('No. of billing cycle is') {{ $billingCycle }}</p>
                        <input type="hidden" id="next_schedule"
                            value="{{ $schedule->issue_date->translatedFormat(company()->date_format) }}">
                    </div>

                </div>

                <!-- CANCEL SAVE START -->
                <x-form-actions class="c-inv-btns d-block d-lg-flex d-md-flex">
                    <x-forms.button-primary data-type="save" class="save-form mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>

                    <x-forms.button-cancel :link="route('kontrak.index')" class="border-0 ">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
                <!-- CANCEL SAVE END -->

            </x-form>
            <!-- FORM END -->
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var schedule = @json($schedule);
            var rotation = @json($schedule->rotation);
            var startDate = $('#next_schedule').val();
            var date = moment(startDate, 'DD-MM-YYYY').toDate();
            nextDate(rotation, date)
        });

        if ($('.custom-date-picker').length > 0) {
            datepicker('.custom-date-picker', {
                position: 'bl',
                ...datepickerConfig
            });
        }

        const dp1 = datepicker('#issue_date', {
            position: 'bl',
            onSelect: (instance, date) => {
                var rotation = $('#rotation').val();
                nextDate(rotation, date);
            },
            dateSelected: new Date("{{ str_replace('-', '/', $schedule->issue_date) }}"),
            ...datepickerConfig
        });

        $('.save-form').click(function() {
            $.easyAjax({
                url: "{{ route('kontrak.update', $schedule->id) }}",
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                redirect: true,
                data: $('#saveInvoiceForm').serialize(),
            });
        });

        $('body').on('change keyup', '#rotation, #billing_cycle', function() {
            var billingCycle = $('#billing_cycle').val();
            billingCycle != '' ? $('#billing').html("{{ __('modules.recurringSchedule.billingCycle') }}" + ' ' +
                billingCycle) : $('#billing').html('');
            var rotation = $('#rotation').val();

            switch (rotation) {
                case 'daily':
                    var rotationType = "{{ __('app.daily') }}";
                    break;
                case 'weekly':
                    var rotationType = "{{ __('app.every') }}" + ' ' +
                        "{{ __('modules.recurringSchedule.week') }}";
                    break;
                case 'bi-weekly':
                    var rotationType = "{{ __('app.every') }}" + ' ' + "{{ __('app.bi-week') }}";
                    break;
                case 'monthly':
                    var rotationType = "{{ __('app.every') }}" + ' ' + "{{ __('app.month') }}";
                    break;
                case 'quarterly':
                    var rotationType = "{{ __('app.every') }}" + ' ' + "{{ __('app.quarter') }}";
                    break;
                case 'half-yearly':
                    var rotationType = "{{ __('app.every') }}" + ' ' + "{{ __('app.half-year') }}";
                    break;
                case 'annually':
                    var rotationType = "{{ __('app.every') }}" + ' ' + "{{ __('app.year') }}";
                    break;
                default:
            }

            $('#plan').html("{{ __('modules.schedules.customerCharged') }}" + ' ' + rotationType);
            var startDate = $('#issue_date').val();
            var date = moment(startDate, 'DD-MM-YYYY').toDate();
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
            $('#current_date').html("{{ __('modules.recurringSchedule.currentScheduleDate') }}" + ' ' + currentValue);
            $('#next_date').html("{{ __('Next Schedule Date will be') }}" + ' ' + value);
        }
    </script>
@endpush
