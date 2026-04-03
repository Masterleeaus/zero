@php
    $editUnitPermission = user()->permission('edit_work_permits');
    $deleteUnitPermission = user()->permission('delete_work_permits');
@endphp
<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<!-- ROW START -->
<div class="row mt-4">
    <div class="col-xl-7 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <x-cards.data>
            <div class="row mb-3">
                <div class="col-10">
                    <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-0">
                        @lang('trworkpermits::app.transfer.showTransfer') @lang('app.details')
                    </h4>
                </div>
                <div class="col-2 text-right">
                    <div class="dropdown">
                        <button class="btn btn-lg f-14 px-2 py-1 text-dark-grey text-capitalize rounded  dropdown-toggle"
                            type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                            @if ($editUnitPermission == 'all')
                                <a class="dropdown-item openRightModal" data-redirect-url="{{ url()->previous() }}"
                                    href="{{ route('work-permits.edit', $wp->id) }}">@lang('app.edit')</a>
                                <a class="dropdown-item f-14 text-dark"
                                    href="{{ route('work-permits.download', [$wp->id]) }}">
                                    @lang('app.download')</a>
                            @endif
                            @if ($deleteUnitPermission == 'all')
                                <a class="dropdown-item delete-unit">@lang('app.delete')</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <x-cards.data-row :label="__('trworkpermits::app.menu.date')" :value="$wp->date" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.companyName')" :value="$wp->company_name" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.companyAddress')" :value="$wp->company_address" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.projectManj')" :value="$wp->project_manj" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.siteCoor')" :value="$wp->site_coor" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.jenisPekerjaan')" :value="ucwords($wp->jenis_pekerjaan)" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.lingkupPekerjaan')" :value="ucwords(str_replace('-', ' ', $wp->lingkup_pekerjaan))" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.unit')" :value="$wp->unit->unit_name" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.noHP')" :value="$wp->phone" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.dateStart')" :value="$wp->date_start" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.dateEnd')" :value="$wp->date_end" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.detailPekerjaan')" :value="ucwords($wp->detail_pekerjaan)" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.keterangan')" :value="ucwords(str_replace('-', ' ', $wp->keterangan))" html="true" />
            <x-cards.data-row :label="__('trworkpermits::app.menu.created')" :value="$wp->created_at" html="true" />
            <div class="border p-2">
                <x-forms.label fieldId="parent_label" :fieldLabel="__('trworkpermits::app.menu.notes')" fieldName="parent_label">
                </x-forms.label>
                <p class="mb-0">
                    @foreach ($notes as $index => $note)
                        @if ($index > 0)
                            <br>
                        @endif
                        - {{ $note->remark }}
                    @endforeach
                </p>
            </div>
        </x-cards.data>
    </div>
    <div class="col-xl-5 col-lg-12 col-md-12 ">
        <div class="row">
            <div class="col-md-12 mb-3">
                <x-cards.data>
                    <div class="row mb-3">
                        <div class="col-10">
                            <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-0">
                                @lang('app.file')
                            </h4>
                        </div>
                    </div>
                    <div class="row" id="add-btn">
                        <div class="col-md-12">
                            <a class="f-15 f-w-500" href="javascript:;" id="add-task-file"><i
                                    class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')</a>
                        </div>
                    </div>
                    <x-form id="save-taskfile-data-form" class="d-none">
                        <input type="hidden" name="project_id" value="{{ $wp->id }}">
                        <div class="row">
                            <div class="col-md-12">
                                <x-forms.file-multiple :fieldLabel="__('modules.projects.uploadFile')" fieldName="file" fieldId="employee_file" />
                            </div>
                            <div class="col-md-12">
                                <div class="w-100 justify-content-end d-flex mt-2">
                                    <x-forms.button-cancel id="cancel-taskfile" class="border-0">@lang('app.cancel')
                                    </x-forms.button-cancel>
                                </div>
                            </div>
                        </div>
                    </x-form>

                    <div class="d-flex flex-wrap mt-3" id="task-file-list">
                        @forelse($wp->files as $file)
                            <x-file-card :fileName="$file->filename" :dateAdded="$file->created_at->diffForHumans()">
                                @if ($file->icon == 'images')
                                    <img src="{{ $file->file_url }}">
                                @else
                                    <i class="fa {{ $file->icon }} text-lightest"></i>
                                @endif

                                <x-slot name="action">
                                    <div class="dropdown ml-auto file-action">
                                        <button
                                            class="btn btn-lg f-14 p-0 text-lightest text-capitalize rounded  dropdown-toggle"
                                            type="button" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                            aria-labelledby="dropdownMenuLink" tabindex="0">
                                            <a class="cursor-pointer d-block text-dark-grey f-13 pt-3 px-3 "
                                                target="_blank" href="{{ $file->file_url }}">@lang('app.view')</a>

                                            <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 "
                                                href="{{ route('files.download', md5($file->id)) }}">@lang('app.download')</a>
                                            <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-file"
                                                data-row-id="{{ $file->id }}"
                                                href="javascript:;">@lang('app.delete')</a>
                                        </div>
                                    </div>
                                </x-slot>
                            </x-file-card>
                        @empty
                            <div class="align-items-center d-flex flex-column text-lightest p-20 w-100">
                                <i class="fa fa-file-excel f-21 w-100"></i>
                                <div class="f-15 mt-4">
                                    @lang('messages.noFileUploaded')
                                </div>
                            </div>
                        @endforelse
                </x-cards.data>
            </div>
        </div>
        <div class="col-md-12 mb-3">
            <x-cards.data :title="__('trworkpermits::app.menu.statusAproval')">
                @if ($wp->approved_by)
                    <p class="fs-6 m-0">Approved by {{ $wp->approved->name }} at {{ $wp->approved_at }}</p>
                @else
                    Not Yet Approved
                @endif
            </x-cards.data>
        </div>
        <div class="col-md-12 mb-3">
            <x-cards.data :title="__('trworkpermits::app.menu.statusValidasi')">
                @if ($wp->validated_by)
                    <img src="{{ $url }}" heigt="100%" class="mb-2"><br>
                    <div class="border p-2">
                        <x-forms.label fieldId="parent_label" :fieldLabel="__('trworkpermits::app.menu.remark')" fieldName="parent_label">
                        </x-forms.label>
                        <p class="mb-0">
                            {{ $wp->validate_remark }}
                        </p>
                    </div>
                    <p class="fs-6 mt-1 m-0">Validated by {{ $wp->validated->name }} at {{ $wp->validated_at }}
                    </p>
                @else
                    Not Yet Validated
                @endif
            </x-cards.data>
        </div>
    </div>
</div>
<!-- ROW END -->

<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script>
    $('body').on('click', '.delete-unit', function() {
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
                var url = "{{ route('work-permits.destroy', $wp->id) }}";

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
                            window.location.href = response.redirectUrl;
                        }
                    }
                });
            }
        });
    });

    $(document).ready(function() {
        Dropzone.autoDiscover = false;
        taskDropzone = new Dropzone("#employee_file", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('work-permits-file.store') }}",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            paramName: "file",
            maxFilesize: DROPZONE_MAX_FILESIZE,
            maxFiles: 10,
            timeout: 0,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: 10,
            acceptedFiles: DROPZONE_FILE_ALLOW,
            init: function() {
                taskDropzone = this;
            }
        });
        taskDropzone.on('sending', function(file, xhr, formData) {
            var ids = "{{ $wp->id }}";
            formData.append('wp_id', ids);
            $.easyBlockUI();
        });
        taskDropzone.on('uploadprogress', function() {
            $.easyBlockUI();
        });
        taskDropzone.on('completemultiple', function(file) {
            var taskView = JSON.parse(file[0].xhr.response).view;
            taskDropzone.removeAllFiles();
            $.easyUnblockUI();
            $('#task-file-list').html(taskView);
        });
        taskDropzone.on('error', function(file) {});

        $('#add-task-file').click(function() {
            $(this).closest('.row').addClass('d-none');
            $('#save-taskfile-data-form').removeClass('d-none');
        });

        $('#cancel-document').click(function() {
            $('#save-taskfile-data-form').addClass('d-none');
            $('#add-task-file').closest('.row').removeClass('d-none');
        });

        $('body').on('click', '#cancel-taskfile', function() {
            $('#save-taskfile-data-form').toggleClass('d-none');
            $('#add-btn').toggleClass('d-none');
        });


        $('body').on('click', '.delete-file', function() {
            var id = $(this).data('row-id');
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
                    var url = "{{ route('work-permits-file.destroy', ':id') }}";
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
                                $('#task-file-list').html(response.view);
                            }
                        }
                    });
                }
            });
        });

    });
</script>
