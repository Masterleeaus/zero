@php
    $addItemCategoryPermission = user()->permission('manage_item_category');
    $addItemSubCategoryPermission = user()->permission('manage_item_sub_category');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-item-form">
            @include('sections.password-autocomplete-hide')

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.add') @lang('fielditems::app.menu.items')</h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">

                            <input type="hidden" id="hiddenItemId">

                            <div class="col-lg-4 col-md-6">
                                <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name"
                                              fieldRequired="true" :fieldPlaceholder="__('fielditems::placeholders.itemName')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.price')"
                                                fieldName="price" fieldId="price" fieldRequired="true"
                                                :fieldPlaceholder="__('placeholders.price')"
                                                fieldValue="0"/>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.label class="my-3" fieldId=""
                                               :fieldLabel="__('fielditems::modules.itemCategory.itemCategory')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="category_id"
                                            id="item_category_id" data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">
                                                {{ mb_ucwords($category->category_name) }}</option>
                                        @endforeach
                                    </select>

                                    @if ($addItemCategoryPermission == 'all' || $addItemCategoryPermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-category" type="button"
                                                    data-toggle="tooltip"
                                                    data-original-title="{{ __('app.add').' '.__('fielditems::modules.itemCategory.itemCategory') }}"
                                                    class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>


                            <div class="col-lg-4 col-md-6">
                                <x-forms.label class="my-3" fieldId=""
                                               :fieldLabel="__('fielditems::modules.itemCategory.itemSubCategory')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="sub_category_id" id="sub_category_id" data-live-search="true">
                                        <option value="">@lang('fielditems::messages.noItemSubCategoryAdded')</option>
                                        @foreach ($subCategories as $subCategory)
                                            <option value="{{ $subCategory->id }}">
                                                {{ mb_ucwords($subCategory->category_name) }}</option>
                                        @endforeach
                                    </select>

                                    @if ($addItemSubCategoryPermission == 'all' || $addItemSubCategoryPermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-sub-category" type="button" data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('fielditems::modules.itemCategory.itemSubCategory') }}" class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.label class="my-3" fieldId="" :fieldLabel="__('modules.invoices.tax')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="tax[]" id="tax_id"
                                            data-live-search="true" multiple="true">
                                        @foreach ($taxes as $tax)
                                            <option value="{{ $tax->id }}">{{ strtoupper($tax->tax_name) }}:
                                                {{ $tax->rate_percent }}%
                                            </option>
                                        @endforeach
                                    </select>

                                    @if (user()->permission('manage_tax') == 'all')
                                        <x-slot name="append">
                                            <button id="add-tax" type="button"
                                            data-toggle="tooltip"
                                            data-original-title="{{ __('app.add').' '.__('modules.invoices.tax') }}"
                                            class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.text fieldId="hsn_sac_code" :fieldLabel="__('app.hsnSac')"
                                              fieldName="hsn_sac_code"
                                              :fieldPlaceholder="__('placeholders.hsnSac')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.label class="my-3" fieldId="" :fieldLabel="__('modules.unitType.unitType')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="unit_type" id="unit_type_id"
                                            data-live-search="true">
                                        @foreach ($unit_types as $unit_type)
                                            <option @if($unit_type->default == 1) selected @endif value="{{ $unit_type->id }}">{{ ucwords($unit_type->unit_type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.qty_minimal')"
                                                fieldName="qty_minimal" fieldId="qty_minimal" fieldRequired="true"
                                                :fieldPlaceholder="__('placeholders.qty_minimal')"
                                                fieldValue="0"/>
                            </div>

                            <div class="col-lg-3 col-md-6 mt-3">
                                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.purchaseAllow')"
                                                  fieldName="purchase_allow" fieldId="purchase_allow" fieldValue="no"
                                                  fieldRequired="true"/>
                            </div>
                            <div class="col-lg-3 col-md-6 mt-3">
                                <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.downloadable')"
                                                  fieldName="downloadable" fieldId="downloadable" fieldValue="true"
                                                  fieldRequired="true" :popover="__('messages.downloadable')"/>
                            </div>

                            <div class="col-lg-12 col-xl-12  mt-2 downloadable d-none">
                                <x-forms.file class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.downloadableFile')"
                                              fieldName="downloadable_file" fieldId="downloadable_file"
                                              fieldRequired="true"/>
                            </div>
                            <div class="col-md-12 mt-3">
                                <div class="form-group">
                                    <x-forms.label class="my-3" fieldId="description-text"
                                                   :fieldLabel="__('app.description')">
                                    </x-forms.label>
                                    <textarea name="description" id="description-text" rows="4"
                                              class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                                                       :fieldLabel="__('app.add') . ' ' .__('app.file')"
                                                       fieldName="file" fieldId="file-upload-dropzone"/>
                            </div>
                            <input type ="hidden" name="add_more" value="false" id="add_more" />
                        </div>
                    </div>

                </div>

                {{-- <x-forms.custom-field :fields="$fields"></x-forms.custom-field> --}}


                <x-form-actions>
                    <x-forms.button-primary id="save-item" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-secondary class="mr-3" id="save-more-item" icon="check-double">@lang('app.saveAddMore')
                    </x-forms.button-secondary>
                    <x-forms.button-cancel :link="route('items.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function () {

        let defaultImage = '';
        let lastIndex = 0;

        Dropzone.autoDiscover = false;
        //Dropzone class
        itemDropzone = new Dropzone("div#file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('item-files.store') }}",
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
            init: function () {
                itemDropzone = this;
            }
        });
        itemDropzone.on('sending', function (file, xhr, formData) {
            const itemID = $('#hiddenItemId').val();
            formData.append('item_id', itemID);
            formData.append('default_image', defaultImage);
            $.easyBlockUI();
        });
        itemDropzone.on('uploadprogress', function () {
            $.easyBlockUI();
        });
        itemDropzone.on('completemultiple', function () {
            window.location.href = '{{ route("items.index") }}';
        });
        itemDropzone.on('addedfile', function (file) {
            lastIndex++;

            const div = document.createElement('div');
            div.className = 'form-check-inline custom-control custom-radio mt-2 mr-3';
            const input = document.createElement('input');
            input.className = 'custom-control-input';
            input.type = 'radio';
            input.name = 'default_image';
            input.id = 'default-image-' + lastIndex;
            input.value = file.name;
            if (lastIndex == 1) {
                input.checked = true;
            }
            div.appendChild(input);

            var label = document.createElement('label');
            label.className = 'custom-control-label pt-1 cursor-pointer';
            label.innerHTML = "@lang('fielditems::modules.makeDefaultImage')";
            label.htmlFor = 'default-image-' + lastIndex;
            div.appendChild(label);

            file.previewTemplate.appendChild(div);
        });

        $('#item_category_id').change(function (e) {
            let categoryId = $(this).val();
            let url = "{{ route('get_item_sub_categories', ':id') }}";

            url = (categoryId) ? url.replace(':id', categoryId) : url.replace(':id', null);

            $.easyAjax({
                url: url,
                type: "GET",
                success: function (response) {
                    if (response.status == 'success') {
                        var options = [];
                        var rData;
                        rData = response.data;
                        $.each(rData, function (index, value) {
                            var selectData;
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

        $('#save-more-item').click(function () {

            $('#add_more').val(true);

            const url = "{{ route('items.store') }}";
            var data = $('#save-item-form').serialize();

            saveItem(data, url, "#save-more-item");

        });

        $('#save-item').click(function() {

            const url = "{{ route('items.store') }}";
            var data = $('#save-item-form').serialize();

            saveItem(data, url, "#save-item");

        });

        function saveItem(data, url, buttonSelector) {
            $.easyAjax({
                url: url,
                container: '#save-item-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: buttonSelector,
                file: true,
                data: data,
                success: function(response) {
                    if (itemDropzone.getQueuedFiles().length > 0) {
                        itemID = response.itemID
                        defaultImage = response.defaultImage;
                        $('#hiddenItemId').val(itemID);
                        itemDropzone.processQueue();
                    }
                    else if(response.add_more == true) {

                        var right_modal_content = $.trim($(RIGHT_MODAL_CONTENT).html());

                        if(right_modal_content.length) {

                            $(RIGHT_MODAL_CONTENT).html(response.html.html);
                            $('#add_more').val(false);
                        }
                        else {

                            $('.content-wrapper').html(response.html.html);
                            init('.content-wrapper');
                            $('#add_more').val(false);
                        }
                    }

                    else{
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }

                    if (typeof showTable !== 'undefined' && typeof showTable === 'function') {
                            showTable();
                    }

                }
            });
        }

        $('#add-category').click(function () {
            const url = "{{ route('itemCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        })

        $('#add-sub-category').click(function () {
            const url = "{{ route('itemSubCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#add-tax').click(function () {
            const url = "{{ route('taxes.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        init(RIGHT_MODAL);

        $('#downloadable').change(function () {
            if ($(this).is(':checked')) {
                $('.downloadable').removeClass('d-none');
            } else {
                $('.downloadable').addClass('d-none');
            }
        });
    });
</script>
