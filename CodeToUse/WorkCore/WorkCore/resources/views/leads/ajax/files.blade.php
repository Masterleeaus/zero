@php
$addLeadFilePermission = user()->permission('add_lead_files');
@endphp
<!-- ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">
        @if ($addLeadFilePermission == 'all' || $addLeadFilePermission == 'added')
            <div class="d-flex p-20">
                <div class="row">
                    <div class="col-md-12">
                        <a class="f-15 f-w-500" href="javascript:;" id="add-files" data-enquiry-id="{{ $deal->id }}"><i
                                class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.sites.uploadFile')</a>
                    </div>
                </div>
            </div>
        @endif

        <div class="p-20">
            <div id="layout">

                @include('enquiries.enquiry-files.thumbnail-list')
            </div>

        </div>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- ROW END -->


<script>
    var fileLayout = 'thumbnail-list';
    function leadFilesView(layout) {
        $('#layout').html('');
        var leadID = "{{ $deal->id }}";
        fileLayout = layout;
        $.easyAjax({
            type: 'GET',
            url: "{{ route('deal-files.layout') }}",
            disableButton: true,
            blockUI: true,
            data: {
                id: leadID,
                layout: layout
            },
            success: function(response) {
                $('#layout').html(response.html);
                if (layout == 'gridview') {
                    $('#list-tabs').removeClass('btn-active');
                    $('#thumbnail').addClass('btn-active');
                } else {
                    $('#list-tabs').addClass('btn-active');
                    $('#thumbnail').removeClass('btn-active');
                }
            }
        });
    }

    $('body').on('click', '.delete-enquiry-file', function() {
        var id = $(this).data('file-id');
        var deleteView = $(this).data('pk');
        Swal.fire({
            title: "@lang('team chat.sweetAlertTitle')",
            text: "@lang('team chat.removeFileText')",
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
                var url = "{{ route('deal-files.destroy', ':id') }}";
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
                            leadFilesView(fileLayout);
                        }
                    }
                });
            }
        });
    });
</script>
