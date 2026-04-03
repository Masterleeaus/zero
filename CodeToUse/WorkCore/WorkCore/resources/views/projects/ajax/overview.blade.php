<script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
<script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/gauge.js') }}"></script>

@php
$editProjectPermission = user()->permission('edit_projects');
$addPaymentPermission = user()->permission('add_payments');
$projectBudgetPermission = user()->permission('view_project_budget');
$memberIds = $site->members->pluck('user_id')->toArray();
$viewTasksPermission = user()->permission('view_tasks');
@endphp

<div class="d-lg-flex">
    <div class="w-100 py-0 py-lg-3 py-md-0 ">
        <div class="d-flex align-content-center flex-lg-row-reverse mb-4">
            @if (!$site->trashed())
                <div class="ml-lg-3 ml-md-0 ml-0 mr-3 mr-lg-0 mr-md-3">
                    @if ($editProjectPermission == 'all' || ($editProjectPermission == 'added' && $site->added_by == user()->id) || ($site->project_admin == user()->id))
                        <select class="form-control select-picker change-status height-35">
                            @foreach ($projectStatus as $status)
                                <option
                                data-content="<i class='fa fa-circle mr-1 f-15' style='color:{{$status->color}}'></i>{{ $status->status_name == 'finished' ? $status->alias : $status->status_name }}"
                                @if ($site->status == $status->status_name)
                                selected @endif
                                value="{{$status->status_name}}"> {{ $status->status_name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        @foreach ($projectStatus as $status)
                            @if ($site->status == $status->status_name)
                                <div class="bg-white p-2 border rounded">
                                    <i class='fa fa-circle mr-2' style="color:{{$status->color}}"></i>{{ $status->status_name }}
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                <div class="ml-lg-3 ml-md-0 ml-0 mr-3 mr-lg-0 mr-md-3">
                    <div class="dropdown">
                        <button
                            class="btn btn-lg bg-white border height-35 f-15 px-2 py-1 text-dark-grey  rounded  dropdown-toggle"
                            type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            @lang('app.action') <i class="icon-options-vertical icons"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">

                            @if ($editProjectPermission == 'all'
                                || ($site->project_admin == user()->id)
                                || ($editProjectPermission == 'added' && user()->id == $site->added_by)
                                || ($editProjectPermission == 'owned' && user()->id == $site->client_id && in_array('customer', user_roles()))
                                || ($editProjectPermission == 'owned' && in_array(user()->id, $memberIds) && in_array('cleaner', user_roles()))
                                || ($editProjectPermission == 'both' && (user()->id == $site->client_id || user()->id == $site->added_by))
                                || ($editProjectPermission == 'both' && in_array(user()->id, $memberIds) && in_array('cleaner', user_roles())))
                                <a class="dropdown-item openRightModal"
                                    href="{{ route('sites.edit', $site->id) }}">@lang('app.editProject')
                                </a>

                                <a class="dropdown-item"
                                    href="{{ route('front.gantt', $site->hash) }}" target="_blank">
                                    @lang('modules.sites.viewPublicGanttChart')
                                </a>

                                <a class="dropdown-item"
                                    href="{{ url()->temporarySignedRoute('front.taskboard', now()->addDays(\App\Models\GlobalSetting::SIGNED_ROUTE_EXPIRY), $site->hash) }}" target="_blank">
                                    @lang('app.publicTaskBoard')
                                </a>
                                <hr class="my-1">
                            @endif

                            @php $projectPin = $site->pinned() @endphp

                            @if ($projectPin)
                                <a class="dropdown-item" href="javascript:;" id="pinnedItem"
                                    data-pinned="pinned">@lang('app.unpinProject')
                                    </a>
                            @else
                                <a class="dropdown-item" href="javascript:;" id="pinnedItem"
                                    data-pinned="unpinned">@lang('app.pinProject')
                                    </a>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($projectPin)
                    <div class="align-self-center">
                        <span class='badge badge-success'><i class='fa fa-thumbtack'></i> @lang('app.pinned')</span>
                    </div>
                @endif
            @elseif($editProjectPermission == 'all'
            || ($site->project_admin == user()->id)
            || ($editProjectPermission == 'added' && user()->id == $site->added_by)
            || ($editProjectPermission == 'owned' && user()->id == $site->client_id && in_array('customer', user_roles()))
            || ($editProjectPermission == 'owned' && in_array(user()->id, $memberIds) && in_array('cleaner', user_roles()))
            || ($editProjectPermission == 'both' && (user()->id == $site->client_id || user()->id == $site->added_by))
            || ($editProjectPermission == 'both' && in_array(user()->id, $memberIds) && in_array('cleaner', user_roles())))
                <div class="ml-3">
                    <x-forms.button-primary class="restore-site" icon="undo">@lang('app.unarchive')
                    </x-forms.button-primary>
                </div>
            @endif
        </div>
        <!-- PROJECT PROGRESS AND CLIENT START -->
        <div class="row">
            <!-- PROJECT PROGRESS START -->
            <div class="col-md-6 mb-4">
                <x-cards.data :title="__('modules.sites.projectProgress')"
                    otherClasses="d-flex d-xl-flex d-lg-block d-md-flex  justify-content-between align-items-center">

                    <x-gauge-chart id="progressGauge" width="100" :value="$site->completion_percent" />

                    <!-- PROGRESS START DATE START -->
                    <div class="p-start-date mb-xl-0 mb-lg-3">
                        <h5 class="text-lightest f-14 font-weight-normal">@lang('app.startDate')</h5>
                        <p class="f-15 mb-0">{{ $site->start_date->translatedFormat(company()->date_format) }}</p>
                    </div>
                    <!-- PROGRESS START DATE END -->
                    <!-- PROGRESS END DATE START -->
                    <div class="p-end-date">
                        <h5 class="text-lightest f-14 font-weight-normal">@lang('modules.sites.deadline')</h5>
                        <p class="f-15 mb-0">
                            {{ !is_null($site->deadline) ? $site->deadline->translatedFormat(company()->date_format) : '--' }}
                        </p>
                    </div>
                    <!-- PROGRESS END DATE END -->

                </x-cards.data>
            </div>
            <!-- PROJECT PROGRESS END -->
            <!-- CLIENT START -->
            <div class="col-md-6 mb-4">
                @if ((!is_null($site->customer) && in_array('customers', user_modules())) || in_array('customer', user_roles()))
                    <x-cards.data :title="__('app.customer')"
                        otherClasses="d-block d-xl-flex d-lg-block d-md-flex  justify-content-between align-items-center">

                        <div class="p-customer-detail">
                            <div class="card border-0 ">
                                <div class="card-horizontal">

                                    <div class="card-img m-0">
                                        <img class="" src=" {{ $site->customer->image_url }}"
                                            alt="{{ $site->customer->name_salutation }}">
                                    </div>
                                    <div class="card-body border-0 p-0 ml-4 ml-xl-4 ml-lg-3 ml-md-3">
                                        <h4 class="card-title f-15 font-weight-normal mb-0">
                                            @if (!in_array('customer', user_roles()))
                                               <a href="{{ route('customers.show', $site->client_id) }}" class="text-dark">
                                                    {{ $site->customer->name_salutation }}
                                                </a>
                                            @else
                                                {{ $site->customer->name_salutation }}
                                            @endif
                                        </h4>
                                        <p class="card-text f-14 text-lightest mb-0">
                                            {{ $site->customer->clientDetails->company_name }}
                                        </p>
                                        @if ($site->customer->country_id)
                                            <span
                                                class="card-text f-12 text-lightest  d-flex align-items-center">
                                                <span
                                                    class='flag-icon flag-icon-{{ strtolower($site->customer->country->iso) }} mr-2'></span>
                                                {{ $site->customer->country->nicename }}
                                            </span>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>

                        @if( (in_array('admin', user_roles()) && $messageSetting->allow_client_admin == 'yes') ||
                        (in_array('cleaner', user_roles()) && $messageSetting->allow_client_employee == 'yes') )
                            <div class="p-customer-msg mt-4 mt-xl-0 mt-lg-4 mt-md-0">
                                <button type="button" class="btn-secondary rounded f-15" id="new-chat"
                                    data-customer-id="{{ $site->customer->id }}"> <i class="fab fa-whatsapp mr-1"></i>
                                    @lang('app.message')</button>
                            </div>
                        @endif

                    </x-cards.data>
                @else
                    <x-cards.data
                        otherClasses="d-flex d-xl-flex d-lg-block d-md-flex  justify-content-between align-items-center">
                        <x-cards.no-record icon="user" :message="__('team chat.noClientAddedToProject')" />
                    </x-cards.data>
                @endif
            </div>
            <!-- CLIENT END -->
        </div>
        <!-- PROJECT PROGRESS AND CLIENT END -->

        <!-- TASK STATUS AND BUDGET START -->
        <div class="row mb-4">
            <!-- TASK STATUS START -->
            @if($viewTasksPermission != 'none')
                <div class="col-lg-6 col-md-12">
                    <x-cards.data :title="__('app.menu.service jobs')" padding="false" class="pb-3">
                        @if (array_sum($taskChart['values']) > 0)
                            <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen" data-chart-id="service job-chart" data-chart-data="{{ json_encode($taskChart) }}"><i class="fas fa-expand float-right mr-3"></i></a>
                        @endif
                        <x-pie-chart id="service job-chart" :labels="$taskChart['labels']" :values="$taskChart['values']"
                            :colors="$taskChart['colors']" height="200" width="200" />
                    </x-cards.data>
                </div>
            @endif
            <!-- TASK STATUS END -->
            <!-- BUDGET VS SPENT START -->
            @if($projectBudgetPermission == 'all' || ($viewProjectTimelogPermission == 'all' && $viewTasksPermission != 'none') || $viewPaymentPermission == 'all' || $viewExpensePermission == 'all')
            <div class="col-lg-6 col-md-12">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="f-18 f-w-500 mb-4">@lang('app.statistics')</h4>
                    </div>
                </div>
                @if($projectBudgetPermission == 'all' || ($viewProjectTimelogPermission == 'all' && $viewTasksPermission != 'none'))
                    <div class="row mb-4">
                        @if ($projectBudgetPermission == 'all')
                            <div class="col">
                                <x-cards.widget :title="__('modules.sites.projectBudget')"
                                    :value="((!is_null($site->project_budget) && $site->currency) ? currency_format($site->project_budget, $site->currency->id) : '0')"
                                    icon="coins" />
                            </div>
                        @endif

                        @if ($viewProjectTimelogPermission == 'all' && $viewTasksPermission != 'none')
                            <div class="col">
                                <x-cards.widget :title="__('modules.sites.hoursLogged')" :value="$hoursLogged"
                                    icon="clock" />
                            </div>
                        @endif
                    </div>
                @endif
                <div class="row">
                    @if ($viewPaymentPermission == 'all')
                        <div class="col">
                            <x-cards.widget :title="(in_array('customer', user_roles())) ? __('app.spending') : __('app.earnings')"
                                :value="(!is_null($site->currency) ? currency_format($earnings, $site->currency->id) : currency_format($earnings, company()->currency_id))"
                                icon="coins" />
                        </div>
                    @endif

                    @if ($viewExpensePermission == 'all')
                        <div class="col">
                            <x-cards.widget :title="__('modules.sites.expenses_total')"
                                :value="(!is_null($site->currency) ? currency_format($expenses, $site->currency->id) : currency_format($expenses, company()->currency_id))"
                                icon="coins" />
                        </div>
                    @endif
                    @if ($viewPaymentPermission == 'all' && !in_array('customer', user_roles()))
                        <div class="col">
                            <x-cards.widget :title="__('modules.sites.profit')"
                                    :value="(!is_null($site->currency) ? currency_format($profit, $site->currency->id) : currency_format($profit, company()->currency_id))"
                                    icon="coins" />
                        </div>
                    @endif
                </div>
            </div>
            @endif
            <!-- BUDGET VS SPENT END -->
        </div>
        <!-- TASK STATUS AND BUDGET END -->

        <!-- TASK STATUS AND BUDGET START -->
        <div class="row mb-4">
            <!-- BUDGET VS SPENT START -->
            @if($projectBudgetPermission == 'all' || ($viewProjectTimelogPermission == 'all'  && $viewTasksPermission != 'none'))
                <div class="col-md-12">
                    <x-cards.data>
                        <div class="row {{ $projectBudgetPermission == 'all' ? 'row-cols-lg-2' : '' }}">
                            @if ($viewProjectTimelogPermission == 'all'  && $viewTasksPermission != 'none')
                                <div class="col">
                                    <h4 class="f-18 f-w-500 mb-0">@lang('modules.sites.hoursLogged')</h4>
                                    <x-stacked-chart id="service job-chart2" :chartData="$hoursBudgetChart" height="250" />
                                </div>
                            @endif
                            @if ($projectBudgetPermission == 'all')
                                <div class="col">
                                    <h4 class="f-18 f-w-500 mb-0">@lang('modules.sites.projectBudget')</h4>
                                    <x-stacked-chart id="service job-chart3" :chartData="$amountBudgetChart" height="250" />
                                </div>
                            @endif
                        </div>
                    </x-cards.data>
                </div>
            @endif
            <!-- BUDGET VS SPENT END -->
        </div>
        <!-- TASK STATUS AND BUDGET END -->

        <!-- PROJECT DETAILS START -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <x-cards.data :title="__('app.site') . ' ' . __('app.details')"
                    otherClasses="d-flex justify-content-between align-items-center">
                    @if (is_null($site->project_summary))
                        <x-cards.no-record icon="align-left" :message="__('team chat.projectDetailsNotAdded')" />
                    @else
                        <div class="text-dark-grey mb-0 ql-editor p-0">{!! $site->project_summary !!}</div>
                    @endif
                </x-cards.data>
            </div>
        </div>
        <!-- PROJECT DETAILS END -->

        {{-- Custom fields data --}}
        @if (isset($fields) && count($fields) > 0)
            <div class="row mt-4">
                <!-- TASK STATUS START -->
                <div class="col-md-12">
                    <x-cards.data :title="__('modules.customer.clientOtherDetails')">
                        <x-forms.custom-field-show :fields="$fields" :model="$site"></x-forms.custom-field-show>
                    </x-cards.data>
                </div>
            </div>
        @endif

    </div>
</div>

<script>
    $(document).ready(function() {
        $('.change-status').change(function() {
            var status = $(this).val();
            var url = "{{ route('sites.update_status', $site->id) }}";
            var token = '{{ csrf_token() }}'

            $.easyAjax({
                url: url,
                type: "POST",
                container: '.content-wrapper',
                blockUI: true,
                data: {
                    status: status,
                    _token: token
                }
            });
        });

        $('body').on('click', '#pinnedItem', function() {
            var type = $('#pinnedItem').attr('data-pinned');
            var id = '{{ $site->id }}';
            var pinType = 'site';

            var dataPin = type.trim(type);
            if (dataPin == 'pinned') {
                Swal.fire({
                    title: "@lang('team chat.sweetAlertTitle')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('team chat.confirmUnpin')",
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
                        var url = "{{ route('sites.destroy_pin', ':id') }}";
                        url = url.replace(':id', id);

                        var token = "{{ csrf_token() }}";
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: {
                                '_token': token,
                                'type': pinType
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    window.location.reload();
                                }
                            }
                        })
                    }
                });

            } else {
                Swal.fire({
                    title: "@lang('team chat.sweetAlertTitle')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('team chat.confirmPin')",
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
                        var url = "{{ route('sites.store_pin') }}?type=" + pinType;

                        var token = "{{ csrf_token() }}";
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: {
                                '_token': token,
                                'project_id': id
                            },
                            success: function(response) {
                                if (response.status == "success") {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            }
        });

        $('body').on('click', '.restore-site', function() {
            Swal.fire({
                title: "@lang('team chat.sweetAlertTitle')",
                text: "@lang('team chat.unArchiveMessage')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('team chat.confirmRevert')",
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
                    var url = "{{ route('sites.archive_restore', $site->id) }}";

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '#new-chat', function() {
            let clientId = $(this).data('customer-id');
            const url = "{{ route('team chat.create') }}?clientId=" + clientId;
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

    });
</script>
