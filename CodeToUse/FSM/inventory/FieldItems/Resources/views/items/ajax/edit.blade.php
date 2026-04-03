@php
$addItemCategoryPermission = user()->permission('manage_item_category');
$addItemSubCategoryPermission = user()->permission('manage_item_sub_category');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-item-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.edit') @lang('app.menu.items') </h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">

                            <div class="col-md-4">
                                <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name"
                                    fieldRequired="true" :fieldPlaceholder="__('placeholders.itemName')"
                                    :fieldValue="$item->name">
                                </x-forms.text>
                            </div>

                            <div class="col-md-4">
                                <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.price')"
                                    fieldName="price" fieldId="price" :fieldPlaceholder="__('placeholders.price')"
                                    :fieldValue="$item->price" />
                            </div>

                            <div class="col-md-4">
                                <x-forms.label class="mt-3" fieldId="category_id"
                                    :fieldLabel="__('modules.itemCategory.itemCategory')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker height-35" name="category_id"
                                        id="item_category_id" data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @if ($category->id == $item->category_id) selected @endif>{{ mb_ucwords($category->category_name) }}</option>
                                        @endforeach
                                    </select>

                                    @if ($addItemCategoryPermission == 'all' || $addItemCategoryPermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-category" type="button"
                                                class="btn btn-outline-secondary border-grey height-35">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>


                            <div class="col-md-4">
                                <x-forms.label class="my-3" fieldId=""
                                    :fieldLabel="__('modules.itemCategory.itemSubCategory')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="sub_category_id"
                                        id="sub_category_id" data-live-search="true">
                                        <option value="">@lang('messages.noItemSubCategoryAdded')</option>
                                        @if ($item->category_id)
                                            @foreach ($item->category->subCategories as $category)
                                                <option value="{{ $category->id }}" @if ($category->id == $item->sub_category_id) selected @endif>{{ mb_ucwords($category->category_name) }}</option>
                                            @endforeach
                                        @endif
                                    </select>

                                    @if ($addItemSubCategoryPermission == 'all' || $addItemSubCategoryPermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-sub-category" type="button"
                                                class="btn btn-outline-secondary border-grey"
                                                data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.itemCategory.itemSubCategory') }}">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>

                            <div class="col-md-4">
                                <x-forms.label class="my-3" fieldId="multiselect" :fieldLabel="__('modules.invoices.tax')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="tax[]" id="multiselect"
                                        data-live-search="true" multiple="true">
                                        @foreach ($taxes as $tax)
                                            <option value="{{ $tax->id }}" @if (isset($item->taxes) && array_search($tax->id, json_decode($item->taxes)) !== false) selected @endif>
                                                {{ strtoupper($tax->tax_name) }}: {{ $tax->rate_percent }}%
                                            </option>
                                        @endforeach
                                    </select>

                                    @if (user()->permission('manage_tax') == 'all')
                                        <x-slot name="append">
                                            <button id="add-tax" type="button"
                                                class="btn btn-outline-secondary border-grey"
                                                data-toggle="tooltip"
                                            data-original-title="{{ __('app.add').' '.__('modules.invoices.tax') }}">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>

                            <div class="col-md-4">
                                <x-forms.text fieldId="hsn_sac_code" :fieldLabel="__('app.hsnSac')"
                                    fieldName="hsn_sac_code" :fieldPlaceholder="__('placeholders.hsnSac')"
                                    :fieldValue="$item->hsn_sac_code">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.label class="my-3" fieldId="" :fieldLabel="__('modules.unitType.unitType')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="unit_type" id="unit_type_id"
                                            data-live-search="true" multiple="true">
                                        @foreach ($unit_types as $unit_type)
                                            <option value="{{ $unit_type->id }}" @if ($unit_type->id == $item->unit_id) selected @endif>{{ $unit_type->unit_type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.qty_minimal')"
                                    fieldName="qty_minimal" fieldId="qty_minimal" :fieldPlaceholder="__('placeholders.price')"
                                    :fieldValue="$item->qty_minimal" />
                            </div>

                            <div class="col-md-3 col-md-6 mt-3">
                                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.purchaseAllow')"
                                    fieldName="purchase_allow" fieldId="purchase_allow" fieldValue="no"
                                    fieldRequired="true" :checked="$item->allow_purchase == 1" />
                            </div>
                            <div class="col-lg-3 col-md-6 mt-3">
                                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.downloadable')"
                                    fieldName="downloadable" fieldId="downloadable" fieldValue="true"
                                    fieldRequired="true" :popover="__('messages.downloadable')" :checked="$item->downloadable == 1" />
                            </div>

                            <div class="col-lg-12 col-xl-12  mt-2 downloadable {{$item->downloadable ? '' : 'd-none'}}">
                                <x-forms.file class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.downloadableFile')"
                                    fieldName="downloadable_file" fieldId="downloadable_file" fieldRequired="true" :fieldValue="$item->download_file_url" />
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="form-group">
                                    <x-forms.label class="my-3" fieldId="description-text"
                                        :fieldLabel="__('app.description')">
                                    </x-forms.label>
                                    <textarea name="description" id="description-text" rows="4" class="form-control f-14 w-100">{{ $item->description }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.add') . ' ' .__('app.file')"
                                    fieldName="file" fieldId="file-upload-dropzone" />
                            </div>

                        </div>
                    </div>

                </div>

                <x-forms.custom-field :fields="$fields" :model="$item"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-item-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('items.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        var defaultImage = '';
        var lastIndex = 0;
        var mockFile = {!! $images !!};

        Dropzone.autoDiscover = false;
        //Dropzone class
        itemDropzone = new Dropzone("div#file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('item-files.update_images') }}",
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
            acceptedFiles: 'image/*',
            init: function() {
                itemDropzone = this;
            },
            removedfile: function (file) {
                var index = mockFile.findIndex(x => x.name == file.name);
                mockFile.splice(index, 1);

                if(typeof(file.id) != 'undefined') {
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
                            var token = "{{ csrf_token() }}";

                            var url = "{{ route('item-files.destroy', ':id') }}";
                            url = url.replace(':id', file.id);

                            $.easyAjax({
                                type: 'POST',
                                url: url,
                                data: {
                                    '_token': token,
                                    '_method': 'DELETE'
                                },
                                success: function(response) {
                                    //This will manually removed the file
                                    file.previewElement.remove();

                                    if ('{{ $item->default_image }}' == file.hashname) {
                                        let $radio = $('.custom-control-input');
                                        $radio[1].checked = true;
                                    }
                                }
                            });
                        }
                    });

                    return false;
                }

                //This will manually removed the file
                file.previewElement.remove();
            }
        });

        itemDropzone.on('sending', function(file, xhr, formData) {
            var itemID = '{{ $item->id }}';
            formData.append('item_id', itemID);
            formData.append('default_image', defaultImage);

            if (mockFile.length > 0) {
                formData.append('uploaded_files', JSON.stringify(mockFile));
            }

            $.easyBlockUI();
        });

        itemDropzone.on('uploadprogress', function() {
            $.easyBlockUI();
        });

        itemDropzone.on('completemultiple', function() {
            window.location.href = '{{ route("items.index") }}';
        });

        itemDropzone.on('addedfile', function(file) {
            lastIndex++;

            var div = document.createElement('div');
            div.className = 'form-check-inline custom-control custom-radio mt-2';

            var input = document.createElement('input');
            input.className = 'custom-control-input';
            input.type = 'radio';
            input.name = 'default_image';
            input.id = 'default-image-'+lastIndex;
            input.value = file.hashname != undefined ? file.hashname : file.name;
            if (lastIndex == 1) {
                input.checked = true;
            }
            if ('{{ $item->default_image }}' == file.hashname) {
                input.checked = true;
            }
            div.appendChild(input);

            var label = document.createElement('label');
            label.className = 'custom-control-label pt-1 cursor-pointer';
            label.innerHTML = "@lang('modules.makeDefaultImage')";
            label.htmlFor = 'default-image-'+lastIndex;
            div.appendChild(label);

            file.previewTemplate.appendChild(div);
        });

        // Create the mock file:
        mockFile.forEach(file => {
            var path = "{{ asset_url('items/' . '/:file_name') }}";
            path = path.replace(':file_name', file.hashname);

            itemDropzone.emit('addedfile', file);
            itemDropzone.emit('thumbnail', file, path);
            itemDropzone.files.push(file);
            itemDropzone.emit("complete", file);
        });

        itemDropzone.options.maxFiles = itemDropzone.options.maxFiles - mockFile.length;

        itemDropzone.on("maxfilesexceeded", function(file) { this.removeFile(file); });

        $('#save-item-form').click(function() {
            const url = "{{ route('items.update', [$item->id]) }}";

            $.easyAjax({
                url: url,
                container: '#save-item-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-item-form",
                file: true,
                data: $('#save-item-data-form').serialize(),
                success: function(response) {
                    if (itemDropzone.getQueuedFiles().length > 0) {
                        defaultImage = response.defaultImage;
                        itemDropzone.processQueue();
                    }
                    else{
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
        });

        $('#item_category_id').change(function(e) {
            let categoryId = $(this).val();

            var url = "{{ route('get_item_sub_categories', ':id') }}";
            url = url.replace(':id', categoryId);

            $.easyAjax({
                url: url,
                type: "GET",
                success: function(response) {
                    if (response.status == 'success') {
                        var options = [];
                        var rData = [];
                        rData = response.data;
                        $.each(rData, function(index, value) {
                            var selectData = '';
                            selectData = '<option value="' + value.id + '">' + value
                                .category_name + '</option>';
                            options.push(selectData);
                        });

                        $('#sub_category_id').html('<option value="">--</option>' + options);
                        $('#sub_category_id').selectpicker('refresh');
                    }
                }
            })
        });

        $('#add-category').click(function() {
            const url = "{{ route('itemCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        })

        $('#add-sub-category').click(function() {
            const url = "{{ route('itemSubCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#add-tax').click(function() {
            const url = "{{ route('taxes.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        init(RIGHT_MODAL);

        $('#downloadable').change(function() {
            if ($(this).is(':checked')) {
                $('.downloadable').removeClass('d-none');
            } else {
                $('.downloadable').addClass('d-none');
            }
        });
    });
</script>
