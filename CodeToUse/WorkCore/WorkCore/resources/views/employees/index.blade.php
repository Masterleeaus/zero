@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <!-- CLIENT START -->
        <div class="select-box py-2 d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.cleaner')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="cleaner" id="cleaner" data-live-search="true"
                        data-size="8">
                    @if ($cleaners->count() > 1 || in_array('admin', user_roles()))
                        <option value="all">@lang('app.all')</option>
                    @endif
                    @foreach ($cleaners as $cleaner)
                        <x-user-option :user="$cleaner"/>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- CLIENT END -->

        <!-- DESIGNATION START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.role')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="role" id="role">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- DESIGNATION END -->


        <!-- SEARCH BY TASK START -->
        <div class="service job-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                           placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>
        <!-- SEARCH BY TASK END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

        <!-- MORE FILTERS START -->
        <x-filters.more-filter-box>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.zone')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="zone" data-container="body"
                                id="zone">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->team_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('modules.cleaners.reportingTo')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="reporting_employee" id="reporting_employee" data-live-search="true"
                                data-size="8">
                            @if ($cleaners->count() > 1 || in_array('admin', user_roles()))
                                <option value="all">@lang('app.all')</option>
                            @endif
                            @foreach ($cleaners as $cleaner)
                                <x-user-option :user="$cleaner"/>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 "
                       for="usr">@lang('modules.cleaners.role')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="role" id="role" data-container="body">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.status')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="status" id="status" data-container="body">
                            <option value="all">@lang('app.all')</option>
                            <option selected value="active">@lang('app.active')</option>
                            <option value="deactive">@lang('app.inactive')</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('modules.cleaners.gender')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="gender" id="gender" data-container="body">
                            <option value="all">@lang('app.all')</option>
                            <option value="male">@lang('app.male')</option>
                            <option value="female">@lang('app.female')</option>
                            <option value="others">@lang('app.others')</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('modules.cleaners.employmentType')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="employmentType" id="employmentType" data-container="body">
                            <option value="all">@lang('app.all')</option>
                            <option value="probation">@lang('app.onProbation')</option>
                            <option value="internship">@lang('app.onInternship')</option>
                            <option value="notice_period">@lang('app.onNoticePeriod')</option>
                            <option value="new_hires">@lang('app.newHires')</option>
                            <option value="long_standing">@lang('app.longStanding')</option>

                        </select>
                    </div>
                </div>
            </div>

        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->
    </x-filters.filter-box>

@endsection

@php
    $addEmployeePermission = user()->permission('add_employees');
    $addDesignationPermission = user()->permission('add_designation');
    $viewDesignationPermission = user()->permission('view_designation');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Service Job Export Buttons Start -->
        <div class="d-flex justify-content-between action-bar">

            <div id="table-actions" class="d-block d-lg-flex align-items-center">
                @if (checkCompanyCanAddMoreEmployees(user()->company_id))
                @if ($addEmployeePermission == 'all')
                    <x-forms.link-primary :link="route('cleaners.create')" class="mr-3 openRightModal" icon="plus">
                        @lang('app.addEmployee')
                    </x-forms.link-primary>

                    <x-forms.button-secondary class="mr-3 invite-member mb-2 mb-lg-0" icon="plus">
                        @lang('app.inviteEmployee')
                    </x-forms.button-secondary>
                @endif

                @if ($addEmployeePermission == 'all')
                    <x-forms.link-secondary :link="route('cleaners.import')" class="mr-3 openRightModal mb-2 mb-lg-0 d-none d-lg-block"
                                            icon="file-upload">
                        @lang('app.importExcel')
                    </x-forms.link-secondary>
                @endif
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.service jobs.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        <option value="deactive">@lang('app.inactive')</option>
                        <option value="active">@lang('app.active')</option>
                    </select>
                </div>
            </x-datatable.actions>

        </div>
        <!-- Add Service Job Export Buttons End -->
        <!-- Service Job Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Service Job Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>

        var startDate = null;
        var endDate = null;
        var lastStartDate = null;
        var lastEndDate = null;

        @if(request('startDate') != '' && request('endDate') != '' )
            startDate = '{{ request("startDate") }}';
        endDate = '{{ request("endDate") }}';
        @endif

            @if(request('lastStartDate') !=='' && request('lastEndDate') !=='' )
            lastStartDate = '{{ request("lastStartDate") }}';
        lastEndDate = '{{ request("lastEndDate") }}';
        @endif

        $('#cleaners-table').on('preXhr.dt', function (e, settings, data) {
            const status = $('#status').val();
            const cleaner = $('#cleaner').val();
            const role = $('#role').val();
            const gender = $('#gender').val();
            const skill = $('#skill').val();
            const role = $('#role').val();
            const zone = $('#zone').val();
            const employmentType = $('#employmentType').val();
            const reporting_employee = $('#reporting_employee').val();
            const searchText = $('#search-text-field').val();
            data['status'] = status;
            data['cleaner'] = cleaner;
            data['role'] = role;
            data['gender'] = gender;
            data['skill'] = skill;
            data['role'] = role;
            data['zone'] = zone;
            data['employmentType'] = employmentType;
            data['reporting_employee'] = reporting_employee;
            data['searchText'] = searchText;

            /* If any of these following filters are applied, then dashboard conditions will not work  */
            if (status == "all" || cleaner == "all" || role == "all" || role == "all" || searchText == "") {
                data['startDate'] = startDate;
                data['endDate'] = endDate;
                data['lastStartDate'] = lastStartDate;
                data['lastEndDate'] = lastEndDate;
            }

        });

        const showTable = () => {
            window.LaravelDataTables["cleaners-table"].draw(true);
        }

        $('#cleaner, #status, #role, #gender, #skill, #role, #zone, #employmentType, #reporting_employee').on('change keyup',
            function () {
                if ($('#status').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                } else if ($('#cleaner').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                } else if ($('#role').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                } else if ($('#reporting_employee').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                } else if ($('#gender').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                } else if ($('#role').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                } else if ($('#zone').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                }else if ($('#employmentType').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                } else {
                    $('#reset-filters').addClass('d-none');
                }
                showTable();
            });

        $('#search-text-field').on('keyup', function () {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
        });

        $('#reset-filters, #reset-filters-2').click(function () {
            $('#filter-form')[0].reset();
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });


        $('#quick-action-type').change(function () {
            const actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');

                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        $('#quick-action-apply').click(function () {
            const actionValue = $('#quick-action-type').val();
            if (actionValue == 'delete') {
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
                        applyQuickAction();
                    }
                });

            } else {
                applyQuickAction();
            }
        });

        $('body').on('click', '.delete-table-row', function () {
            var id = $(this).data('user-id');
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
                    var url = "{{ route('cleaners.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function (response) {
                            if (response.status == "success") {
                                showTable();
                            }
                        }
                    });
                }
            });
        });

        const applyQuickAction = () => {
            var rowdIds = $("#cleaners-table input:checkbox:checked").map(function () {
                return $(this).val();
            }).get();

            var url = "{{ route('cleaners.apply_quick_action') }}?row_ids=" + rowdIds;

            $.easyAjax({
                url: url,
                container: '#quick-action-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                blockUI: true,
                success: function (response) {
                    if (response.status == 'success') {
                        showTable();
                        resetActionButtons();
                        deSelectAll();
                        $('#quick-action-form').hide();
                    }
                }
            })
        };


        $('body').on('change', '.assign_role', function () {
            var id = $(this).data('user-id');
            var role = $(this).val();
            var token = "{{ csrf_token() }}";

            if (typeof id !== 'undefined') {
                $.easyAjax({
                    url: "{{ route('cleaners.assign_role') }}",
                    type: "POST",
                    blockUI: true,
                    container: '#cleaners-table',
                    data: {
                        role: role,
                        userId: id,
                        _token: token
                    },
                    success: function (response) {
                        if (response.status == "success") {
                            window.LaravelDataTables["cleaners-table"].draw(true);
                        }
                    }
                })
            }

        });

        $('#role-setting').click(function () {
            const url = "{{ route('roles.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        })

        $('.zone-setting').click(function () {
            const url = "{{ route('zones.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });
    </script>
@endpush
