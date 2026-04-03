<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<div class="row">
    <div class="col-sm-12">
        <x-form id="save-unit-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('trworkpermits::app.workpermits.add')</h4>
                <div class="row p-20">
                    <div class="col-md-12">
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
                    </div>

                    <div class="col-md-2">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('trworkpermits::app.menu.date')"
                            fieldRequired="true"></x-forms.label>
                        <div class="input-group">
                            <input type="text" name="date" class="px-6 form-control height-35 rounded p-0 f-15"
                                value="{{ now(company()->timezone)->translatedFormat('Y-m-d H:i:s') }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.text fieldId="company_name" :fieldLabel="__('trworkpermits::app.menu.companyName')" fieldName="company_name"
                            fieldRequired="true" :fieldPlaceholder="__('')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-7">
                        <x-forms.text fieldId="company_address" :fieldLabel="__('trworkpermits::app.menu.companyAddress')" fieldName="company_address"
                            fieldRequired="true" :fieldPlaceholder="__('')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-2">
                        <x-forms.label class=" mt-3" fieldId="phone" :fieldLabel="__('trworkpermits::app.menu.noHP')"
                            fieldRequired="true"></x-forms.label>
                        <div class="input-group">
                            <input type="number" id="phone" name="phone"
                                class="px-6 form-control height-35 rounded p-0 f-15" placeholder="">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.text fieldId="project_manj" :fieldLabel="__('trworkpermits::app.menu.projectManj')" fieldName="project_manj"
                            fieldRequired="true" :fieldPlaceholder="__('')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-3">
                        <x-forms.text fieldId="site_coor" :fieldLabel="__('trworkpermits::app.menu.siteCoor')" fieldName="site_coor" fieldRequired="true"
                            :fieldPlaceholder="__('')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('trworkpermits::app.menu.jenisPekerjaan')"
                            fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="jenis_pekerjaan" id="jenis_pekerjaan"
                            data-live-search="true">
                            <option value="renovasi">Renovasi</option>
                            <option value="non-renovasi">Non Renovasi</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('trworkpermits::app.menu.lingkupPekerjaan')"
                            fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="lingkup_pekerjaan" id="lingkup_pekerjaan"
                            data-live-search="true">
                            <option value="interior">Interior</option>
                            <option value="mechanical">Mechanical</option>
                            <option value="electrical">Electrical</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('trworkpermits::app.menu.unit')"
                            fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="unit_id" id="unit_id"
                            data-live-search="true">
                            @foreach ($units as $items)
                                <option value="{{ $items->id }}">{{ $items->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="date_start" :fieldLabel="__('trworkpermits::app.menu.dateStart')"
                            fieldRequired="true"></x-forms.label>
                        <div class="input-group">
                            <input type="text" id="date_start" name="date_start"
                                class="px-6 form-control height-35 rounded p-0 f-15"
                                value="{{ now(company()->timezone)->translatedFormat('d-m-Y') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="date_end" :fieldLabel="__('trworkpermits::app.menu.dateEnd')"
                            fieldRequired="true"></x-forms.label>
                        <div class="input-group">
                            <input type="text" id="date_end" name="date_end"
                                class="px-6 form-control height-35 rounded p-0 f-15"
                                value="{{ now(company()->timezone)->translatedFormat('d-m-Y') }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <x-forms.label class="my-3" fieldId="description-text" :fieldLabel="__('trworkpermits::app.menu.detailPekerjaan')"
                                fieldRequired="true">
                            </x-forms.label>
                            <textarea name="detail_pekerjaan" id="description-text" rows="4" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.add') . ' ' . __('app.file')" fieldName="file"
                            fieldId="file-upload-dropzone" />
                        <input type="hidden" name="wp_id" id="wp_id">
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-unit-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('work-permits.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>
<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script>
    $(document).ready(function() {
        const dp2 = datepicker('#date_start', {
            position: 'bl',
            ...datepickerConfig
        });

        const dp3 = datepicker('#date_end', {
            position: 'bl',
            ...datepickerConfig
        });

        Dropzone.autoDiscover = false;
        myDropzone = new Dropzone("div#file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('work-permits-file.multiple_upload') }}",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            paramName: "file",
            maxFilesize: DROPZONE_MAX_FILESIZE,
            maxFiles: 10,
            autoProcessQueue: false,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: 10,
            acceptedFiles: DROPZONE_FILE_ALLOW,
            init: function() {
                myDropzone = this;
            }
        });
        myDropzone.on('sending', function(file, xhr, formData) {
            var ids = $('#wp_id').val();
            formData.append('wp_id', ids);
        });
        myDropzone.on('uploadprogress', function() {
            $.easyBlockUI();
        });
        myDropzone.on('completemultiple', function() {
            var msgs = "@lang('messages.updateSuccess')";
            window.location.href = "{{ route('work-permits.index') }}"
        });

        $('#save-unit-form').click(function() {
            const url = "{{ route('work-permits.store') }}";
            $.easyAjax({
                url: url,
                container: '#save-unit-data-form',
                type: "POST",
                blockUI: true,
                redirect: true,
                file: true,
                buttonSelector: "#save-unit-form",
                data: $('#save-unit-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        if (myDropzone.getQueuedFiles().length > 0) {
                            $('#wp_id').val(response.wp_id);
                            myDropzone.processQueue();
                        } else {
                            window.location.href =
                                "{{ route('work-permits.index') }}";
                        }
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
