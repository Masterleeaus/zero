@php
    $addProjectTicketPermission = user()->permission('add_tickets');
@endphp

<!-- ROW START -->
<div class="row pb-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mt-3 mt-lg-5 mt-md-5">
        <!-- Add Service Job Export Buttons Start -->
        <div class="d-flex" id="table-actions">
            @if (($addProjectTicketPermission == 'all' || $addProjectTicketPermission == 'added' || $site->project_admin == user()->id) && !$site->trashed())
                <x-forms.link-primary :link="route('issues / support.create').'?site='.$site->id"
                    class="mr-3 openRightModal" icon="plus" data-redirect-url="{{ url()->full() }}">
                    @lang('modules.issues / support.addTicket')
                </x-forms.link-primary>
            @endif

        </div>
        <!-- Add Service Job Export Buttons End -->

        <div class="d-flex justify-content-between">
            <form action="" class="flex-grow-1 " id="filter-form">
                <div class="d-flex mt-3">
                     <!-- STATUS START -->
                     <div class="select-box py-2 px-0 mr-3">
                        <x-forms.label :fieldLabel="__('app.status')" fieldId="status" />
                        <select class="form-control select-picker" name="status" id="status">
                            <option {{ request('status') == 'all' ? 'selected' : '' }} value="all">@lang('app.all')</option>
                            <option {{ (request('status') == 'open' || request('status') == '' ) ? 'selected' : '' }} value="open">
                                @lang('modules.issues / support.totalOpenTickets')</option>
                            <option {{ request('status') == 'pending' ? 'selected' : '' }} value="pending">
                                @lang('modules.issues / support.totalPendingTickets')</option>
                            <option {{ request('status') == 'resolved' ? 'selected' : '' }} value="resolved">
                                @lang('modules.issues / support.totalResolvedTickets')</option>
                            <option {{ request('status') == 'closed' ? 'selected' : '' }} value="closed">
                                @lang('modules.issues / support.totalClosedTickets')</option>
                        </select>
                    </div>
                    <!-- STATUS END -->

                    <!-- SEARCH BY TASK START -->
                    <div class="select-box py-2 px-lg-2 px-md-2 px-0 mr-3">
                        <x-forms.label fieldId="search-text-field"/>
                        <div class="input-group bg-grey rounded">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-additional-grey">
                                    <i class="fa fa-search f-13 text-dark-grey"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control f-14 p-1 height-35 border" id="search-text-field"
                                placeholder="@lang('app.startTyping')">
                        </div>
                    </div>
                    <!-- SEARCH BY TASK END -->

                    <!-- RESET START -->
                    <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 mt-4">
                        <x-forms.button-secondary class="btn-xs d-none height-35 mt-2" id="reset-filters"
                            icon="times-circle">
                            @lang('app.clearFilters')
                        </x-forms.button-secondary>
                    </div>
                    <!-- RESET END -->
                </div>
            </form>

            @if (!in_array('customer', user_roles()))
                <x-datatable.actions  class="mt-5">
                    <div class="select-status mr-3 pl-3">
                        <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                            <option value="">@lang('app.selectAction')</option>
                            <option value="change-status">@lang('modules.service jobs.changeStatus')</option>
                            <option value="delete">@lang('app.delete')</option>
                        </select>
                    </div>
                    <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                        <select name="status" class="form-control select-picker">
                            <option value="open">@lang('app.open')</option>
                            <option value="pending">@lang('app.pending')</option>
                            <option value="resolved">@lang('app.resolved')</option>
                            <option value="closed">@lang('app.closed')</option>
                        </select>
                    </div>
                </x-datatable.actions>
            @endif
        </div>

        <!-- Service Job Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}



        </div>
        <!-- Service Job Box End -->
    </div>
</div>

@include('sections.datatable_js')

<script>
    $('#issue / support-table').on('preXhr.dt', function(e, settings, data) {

        var status = $('#status').val();
        if (status == "") {
            status = 0;
        }

        data['ticketStatus'] = status;
        data['projectID'] = "{{ $site->id }}";
        data['searchText'] = $('#search-text-field').val();
    });
    const showTable = () => {
        window.LaravelDataTables["issue / support-table"].draw(true);
    }

    $('#search-text-field').on('keyup', function() {
        if ($('#search-text-field').val() != "") {
            $('#reset-filters').removeClass('d-none');
            showTable();
        }
    });
    $('#status').on('change keyup', function() {
            if ($('#status').val() != "not finished") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
    });
    $('#reset-filters,#reset-filters-2').click(function() {
        $('#filter-form')[0].reset();
        $('#filter-form .select-picker').selectpicker("refresh");
        $('#reset-filters').addClass('d-none');
        showTable();
    });


    $('#quick-action-type').change(function() {
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

        $('#quick-action-apply').click(function() {
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

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('issue / support-id');
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
                    var url = "{{ route('issues / support.destroy', ':id') }}";
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
                                showTable();
                            }
                        }
                    });
                }
            });
        });

        $('#issue / support-table').on('change', '.change-status', function() {
            var url = "{{ route('issues / support.change-status') }}";
            var token = "{{ csrf_token() }}";
            var id = $(this).data('issue / support-id');
            var status = $(this).val();

            if (id != "" && status != "") {
                $.easyAjax({
                    url: url,
                    type: "POST",
                    container: '.content-wrapper',
                    blockUI: true,
                    data: {
                        '_token': token,
                        ticketId: id,
                        status: status,
                    },
                    success: function(data) {
                        showTable();
                    }
                });

            }
        });

        const applyQuickAction = () => {
            var rowdIds = $("#issue / support-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('issues / support.apply_quick_action') }}?row_ids=" + rowdIds;

            $.easyAjax({
                url: url,
                container: '#quick-action-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        showTable();
                        resetActionButtons();
                        deSelectAll();
                        $('#quick-action-form').hide();
                    }
                }
            })
        };

</script>
