@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <style>
        .filter-box {
            z-index: 2;
        }
    </style>
@endpush

@php
    $addUnitPermission = user()->permission('add_trinoutpermit');
@endphp

@section('filter-section')
    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->
        <!-- STATUS APPROVAL START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('trinoutpermit::app.menu.statusApprove')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status_approve" id="status_approve">
                    <option value="all" selected>@lang('app.all')</option>
                    <option value="approved">@lang('trinoutpermit::app.menu.approve')</option>
                    <option value="notApprove">@lang('trinoutpermit::app.menu.notApprove')</option>
                </select>
            </div>
        </div>
        <!-- STATUS APPROVAL END -->
        <!-- STATUS APPROVAL START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('trinoutpermit::app.menu.statusApproveBm')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status_approve_bm" id="status_approve_bm">
                    <option value="all" selected>@lang('app.all')</option>
                    <option value="approved">@lang('trinoutpermit::app.menu.approve')</option>
                    <option value="notApprove">@lang('trinoutpermit::app.menu.notApprove')</option>
                </select>
            </div>
        </div>
        <!-- STATUS APPROVAL END -->
        <!-- STATUS VALIDATE START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('trinoutpermit::app.menu.statusValidate')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status_validate" id="status_validate">
                    <option value="all" selected>@lang('app.all')</option>
                    <option value="validated">@lang('trinoutpermit::app.menu.validated')</option>
                    <option value="notValidated">@lang('trinoutpermit::app.menu.notValidate')</option>
                </select>
            </div>
        </div>
        <!-- STATUS VALIDATE END -->
        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex pr-lg-2 py-1 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group rounded">
                    <div class="input-group-prepend margin-9">
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
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="usr">@lang('trinoutpermit::app.menu.keterangan')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="ket" id="ket" data-live-search="true"
                            data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="masuk-ke-unit">Masuk ke dalam Unit</option>
                            <option value="keluar-dari-unit">Keluar dari dalam Unit</option>
                            <option value="pindah-antar-unit">Pindah antar Unit</option>
                        </select>
                    </div>
                </div>
            </div>
        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->
    </x-filters.filter-box>
@endsection


@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addUnitPermission == 'all' || $addUnitPermission == 'owned')
                    <x-forms.link-primary :link="route('trinoutpermit.create')" class="mr-3 openRightModal float-left" icon="plus">
                        @lang('trinoutpermit::app.trinoutpermit.addTrInOutPermit')
                    </x-forms.link-primary>
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
            </x-datatable.actions>
        </div>

        <!-- leave table Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- leave table End -->

    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#Unit-table').on('preXhr.dt', function(e, settings, data) {
            const ket = $('#ket').val();
            const searchText = $('#search-text-field').val();
            const status_approve = $('#status_approve').val();
            const status_approve_bm = $('#status_approve_bm').val();
            const status_validate = $('#status_validate').val();
            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var startDate = $('#datatableRange').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            @if (request('startDate') != '' && request('endDate') != '')
                startDate = '{{ request('startDate') }}';
                endDate = '{{ request('endDate') }}';
            @endif

            data['status_validate'] = status_validate;
            data['status_approve'] = status_approve;
            data['status_approve_bm'] = status_approve_bm;
            data['ket'] = ket;
            data['searchText'] = searchText;
            data['startDate'] = startDate;
            data['endDate'] = endDate;

            /* If any of these following filters are applied, then dashboard conditions will not work  */
            if (ket == "all" || searchText == "") {

            }
        });

        const showTable = () => {
            window.LaravelDataTables["Unit-table"].draw(false);
        }

        $('#status_approve').on('change keyup',
            function () {
                if ($('#status_approve').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else {
                    $('#reset-filters').addClass('d-none');
                    showTable();
                }
        });

        $('#status_approve_bm').on('change keyup',
            function () {
                if ($('#status_approve_bm').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else {
                    $('#reset-filters').addClass('d-none');
                    showTable();
                }
        });

        $('#status_validate').on('change keyup',
            function () {
                if ($('#status_validate').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else {
                    $('#reset-filters').addClass('d-none');
                    showTable();
                }
        });

        $('#ket').on('change keyup',
            function() {
                if ($('#ket').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    showTable();
                } else {
                    $('#reset-filters').addClass('d-none');
                    showTable();
                }
            });

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
        });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            $('.filter-box #ket').val('all');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('unit-id');
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
                    var url = "{{ route('trinoutpermit.destroy', ':id') }}";
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
                        success: function(response) {
                            if (response.status == "success") {
                                showTable();
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.approve', function() {
            var id = $(this).data('unit-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.confirmApprove')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirm')",
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
                    var url = "{{ route('trinoutpermit.approved', ':id') }}";
                    url = url.replace(':id', id);
                    window.location.href = url;
                }
            });
        });

        $('body').on('click', '.approve_bm', function() {
            var id = $(this).data('unit-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.confirmApprove')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirm')",
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
                    var url = "{{ route('trinoutpermit.approved_bm', ':id') }}";
                    url = url.replace(':id', id);
                    window.location.href = url;
                }
            });
        });

        $('#quick-action-type').change(function() {
            const actionValue = $(this).val();

            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        $('#quick-action-apply').click(function() {
            const actionValue = $('#quick-action-type').val();

            if (actionValue === 'delete') {
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
                        applyQuickAction();
                    }
                });

            } else {
                applyQuickAction();
            }
        });

        const applyQuickAction = () => {
            const rowdIds = $("#Unit-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            const url = "{{ route('trinoutpermit.apply_quick_action') }}?row_ids=" + rowdIds;

            $.easyAjax({
                url: url,
                container: '#quick-action-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        showTable();
                        resetActionButtons();
                        deSelectAll();
                        $('#quick-action-form').hide();
                    }
                }
            })
        };
    </script>
@endpush
