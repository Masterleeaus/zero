<style>
    #logo {
        height: 50px;
    }
</style>

<div class="d-lg-flex">
    <div class="w-100 py-0 py-md-0 ">
        <x-cards.data :title="__('app.recurring') . ' ' . __('app.details')" class=" mt-4">
            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                    @lang('kontrak::app.menu.total')</p>
                <p class="mb-0 text-dark-grey f-14 ">
                    @if (!is_null($schedule->billing_cycle))
                        {{ $schedule->recurrings->count() }}/{{ $schedule->billing_cycle }}
                    @else
                        {{ $schedule->recurrings->count() }}/<span class="px-1"><label
                                class="badge badge-primary">@lang('app.infinite')</label></span>
                    @endif
                </p>
            </div>
            @if (count($schedule->recurrings) > 0)
                <x-cards.data-row :label="__('First Schedule Date')" :value="Carbon\Carbon::parse($schedule->recurrings->last()->issue_date)->translatedFormat(company()->date_format)" />
            @else
                <x-cards.data-row :label="__('First Schedule Date')" :value="$schedule->issue_date
                    ? $schedule->issue_date->translatedFormat(company()->date_format)
                    : '----'" />
            @endif

            <x-cards.data-row :label="__('Next Schedule') . ' ' . __('app.date')" :value="$schedule->next_schedule_date
                ? $schedule->next_schedule_date->translatedFormat(company()->date_format)
                : '----'" />
        </x-cards.data>
    </div>
</div>
<div class="d-lg-flex">
    <div class="w-100 py-0 py-lg-4 py-md-0 ">
        <div class="card border-0 schedule">
            <!-- CARD BODY START -->
            <div class="card-body">
                <div class="invoice-table-wrapper">
                    <table width="100%" class="">
                        <tr class="inv-logo-heading">
                            <td><img src="{{ invoice_setting()->logo_url }}"
                                    alt="{{ mb_ucwords(company()->company_name) }}" id="logo" /></td>
                            <td align="right"
                                class="font-weight-bold f-21 text-dark text-uppercase mt-4 mt-lg-0 mt-md-0">
                                @lang('kontrak::modules.recContract')</td>
                        </tr>
                        <tr class="inv-num">
                            <td class="f-14 text-dark">
                                <p class="mt-3 mb-0">
                                    {{ mb_ucwords(company()->company_name) }}<br>
                                    @if (!is_null($settings))
                                        {!! nl2br(default_address()->address) !!}<br>
                                        {{ company()->company_phone }}
                                    @endif
                                </p><br>
                            </td>
                            <td align="right">
                                <table class="inv-num-date text-dark f-13 mt-3">
                                    <tr>
                                        <td class="bg-light-grey border-right-0 f-w-500">
                                            @lang('kontrak::modules.firstSchedule')</td>
                                        <td class="border-left-0">
                                            {{ $schedule->issue_date->translatedFormat(company()->date_format) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bg-light-grey border-right-0 f-w-500">@lang('kontrak::modules.nextSchedule')</td>
                                        <td class="border-left-0">
                                            {{ $schedule->next_schedule_date->translatedFormat(company()->date_format) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td height="20"></td>
                        </tr>
                    </table>
                </div>
            </div>
            <!-- CARD BODY END -->

        </div>
    </div>
</div>
