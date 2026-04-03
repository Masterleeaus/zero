<!-- CREATE INVOICE START -->
<div class="bg-white rounded b-shadow-4 create-inv">
    <!-- HEADING START -->
    <div class="px-lg-4 px-md-4 px-3 py-3">
        <h4 class="mb-0 f-21 font-weight-normal text-capitalize">@lang('trpackage::app.receive.addReceive') @lang('app.details')</h4>
    </div>
    <!-- HEADING END -->
    <hr class="m-0 border-top-grey">
    <!-- FORM START -->
    <x-form class="c-inv-form" id="saveInvoiceForm">
        <div class="row px-3 py-3">
            <div class="col-lg-9">
                <div class="row">
                    <div class="col-md-2">
                        <x-forms.label class=" mt-3" fieldId="date" :fieldLabel="__('trpackage::app.menu.date')" fieldRequired="true">
                        </x-forms.label>
                        <div class="input-group">
                            <input type="text" id="date" name="tanggal_diterima"
                                class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                placeholder="@lang('placeholders.date')"
                                value="{{ now(company()->timezone)->translatedFormat('d-m-Y') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('trpackage::app.menu.namaEkspedisi')" fieldName="parent_label">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="ekspedisi" id="ekspedisi"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($ekspedisi as $items)
                                    <option value="{{ $items->id }}">{{ $items->name }}</option>
                                @endforeach
                            </select>
                            <x-slot name="append">
                                <button id="add-ekspedisi" type="button" data-toggle="tooltip"
                                    data-original-title="{{ __('app.add') . ' ' . __('trpackage::modules.ekspedisi') }}"
                                    class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                            </x-slot>
                        </x-forms.input-group>
                    </div>
                    <div class="col-md-4">
                        <x-forms.text fieldId="name" :fieldLabel="__('trpackage::app.menu.namaPengirim')" fieldName="nama_pengirim" fieldRequired="true"
                            :fieldPlaceholder="__('')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="no_hp" :fieldLabel="__('trpackage::app.menu.hpPengirim')" fieldRequired="true">
                        </x-forms.label>
                        <div class="input-group">
                            <input type="number" id="no_hp" name="no_hp_pengirim"
                                class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                placeholder="">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bootstrap-timepicker timepicker">
                            <x-forms.text :fieldLabel="__('trpackage::app.menu.jamAmbil')" :fieldPlaceholder="__('placeholders.hours')" fieldName="jam" fieldId="awal"
                                fieldRequired="true" />
                        </div>
                    </div>
                    <div class="col-md-10">
                        <x-forms.text fieldId="name" :fieldLabel="__('trpackage::app.menu.note')" fieldName="catatan_penerima" fieldRequired="true"
                            :fieldPlaceholder="__('')">
                        </x-forms.text>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <x-forms.file allowedFileExtensions="png jpg jpeg svg" class="mr-0 mr-lg-2 mr-md-2 cropper"
                    :fieldLabel="__('trpackage::app.menu.fotoPenerima')" fieldName="foto_penerima" fieldId="image" fieldHeight="119" />
            </div>
        </div>
        <!-- CLIENT, PROJECT, GST, BILLING, SHIPPING ADDRESS END -->
        <hr class="m-0 border-top-grey">

        <div id="sortable">
            <!-- DESKTOP DESCRIPTION TABLE START -->
            <div class="d-flex px-4 pt-3 c-inv-desc item-row">
                <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                    <table width="100%">
                        <tbody>
                            <tr class="text-dark-grey font-weight-bold f-14">
                                <td width="25%" class="border-0 inv-desc-mbl btlr">
                                    @lang('trpackage::app.menu.unit')
                                </td>
                                <td width="25%" class="border-0" id="type">
                                    @lang('trpackage::app.menu.jenisBarang')
                                </td>
                                <td width="50%" class="border-0">
                                    @lang('trpackage::app.menu.namaPenerima')
                                </td>
                            </tr>
                            <tr>
                                <td class="border-0">
                                    <div class="select-others height-35 rounded border-0">
                                        <select class="form-control select-picker" name="unit_id[]" id="unit_id"
                                            data-live-search="true">
                                            <option value="">--</option>
                                            @foreach ($units as $items)
                                                <option value="{{ $items->id }}">{{ $items->unit_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td class="border-0">
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" name="jenis_barang[]" id="jenis"
                                            data-live-search="true">
                                            <option value="">--</option>
                                            @foreach ($typePackage as $items)
                                                <option value="{{ $items->id }}">{{ mb_ucwords($items->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-slot name="append">
                                            <button id="add-type" type="button" data-toggle="tooltip"
                                                data-original-title="{{ __('app.add') . ' ' . __('trpackage::app.menu.jenisBarang') }}"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    </x-forms.input-group>
                                </td>
                                <td class="border-0">
                                    <input type="text" class="form-control f-14 height-35 rounded w-100"
                                        name="nama_penerima[]">
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <a href="javascript:;"
                        class="d-flex align-items-center justify-content-center ml-3 remove-item"><i
                            class="fa fa-times-circle f-20 text-lightest"></i></a>
                </div>
            </div>
            <!-- DESKTOP DESCRIPTION TABLE END -->

        </div>
        <!--  ADD ITEM START-->
        <div class="row px-lg-4 px-md-4 px-3 pb-3 pt-0 mb-3  mt-2">
            <div class="col-md-12">
                <a class="f-15 f-w-500" href="javascript:;" id="add-item"><i
                        class="icons icon-plus font-weight-bold mr-1"></i>@lang('modules.invoices.addItem')</a>
            </div>
        </div>

        <!-- CANCEL SAVE START -->
        <x-form-actions class="c-inv-btns d-block d-lg-flex d-md-flex">
            <x-forms.button-primary data-type="save" class="save-form mr-3" icon="check">@lang('app.save')
            </x-forms.button-primary>

            <x-forms.button-cancel :link="route('receive.index')" class="border-0 ">@lang('app.cancel')
            </x-forms.button-cancel>
        </x-form-actions>
        <!-- CANCEL SAVE END -->

    </x-form>
    <!-- FORM END -->
</div>
<script>
    $(document).ready(function() {
        if ($('.custom-date-picker').length > 0) {
            datepicker('.custom-date-picker', {
                position: 'bl',
                ...datepickerConfig
            });
        }

        const dp1 = datepicker('#date', {
            position: 'bl',
            ...datepickerConfig
        });

        $('#awal').timepicker({
            @if (company()->time_format == 'H:i')
                showMeridian: false,
            @endif
        }).on('hide.timepicker', function(e) {
            calculateTime();
        });

        $(document).on('click', '#add-item', function() {

            var i = $(document).find('.item_name').length;
            var item = `
            <div class="d-flex px-4 c-inv-desc item-row">
                <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                    <table width="100%">
                        <tbody>
                            <tr>
                                <td width="25%" class="border-0">
                                    <div class="select-others height-35 rounded border-0 select-picker">
                                        <select class="form-control select-picker height-35 f-14" name="unit_id[]" id="unit_ids"
                                        data-live-search="true">
                                            <option value="">--</option>
                                            @foreach ($units as $items)
                                                <option value="{{ $items->id }}">{{ $items->unit_name }}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </td>
                                <td width="25%" class="border-0">
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" name="jenis_barang[]" id="jenis"
                                            data-live-search="true">
                                            <option value="">--</option>
                                            @foreach ($typePackage as $items)
                                                    <option value="{{ $items->id }}">{{ mb_ucwords($items->name) }}</option>
                                            @endforeach
                                        </select>
                                        <x-slot name="append">
                                            <button id="add-type" type="button" data-toggle="tooltip"
                                                data-original-title="{{ __('app.add') . ' ' . __('trpackage::app.menu.jenisBarang') }}"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    </x-forms.input-group>
                                </td>
                                <td width="50%" class="border-0">
                                    <input type="text" class="form-control f-14 height-35 rounded w-100" name="nama_penerima[]">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <a href="javascript:;" class="d-flex align-items-center justify-content-center ml-3 remove-item"><i class="fa fa-times-circle f-20 text-lightest"></i></a>
            </div>`;
            $(item).hide().appendTo("#sortable").fadeIn(500);
            $('select').selectpicker('refresh');
        });

        $('#saveInvoiceForm').on('click', '.remove-item', function() {
            $(this).closest('.item-row').fadeOut(300, function() {
                $(this).remove();
                calculateTotal();
            });
        });

        $('.save-form').click(function() {
            if (KTUtil.isMobileDevice()) {
                $('.desktop-description').remove();
            } else {
                $('.mobile-description').remove();
            }
            $.easyAjax({
                url: "{{ route('receive.store') }}",
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                redirect: true,
                file: true, // Commented so that we dot get error of Input variables exceeded 1000
                data: $('#saveInvoiceForm').serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            })
        });

        $('#saveInvoiceForm').on('click', '.remove-item', function() {
            $(this).closest('.item-row').fadeOut(300, function() {
                $(this).remove();
            });
        });

        $('#add-ekspedisi').click(function() {
            const url = "{{ route('ekspedisi.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#add-type').click(function() {
            const url = "{{ route('type.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        init(RIGHT_MODAL);
    });
</script>
