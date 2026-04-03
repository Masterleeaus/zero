<!-- CREATE INVOICE START -->
<div class="bg-white rounded b-shadow-4 create-inv">
    <!-- HEADING START -->
    <div class="px-lg-4 px-md-4 px-3 py-3">
        <h4 class="mb-0 f-21 font-weight-normal text-capitalize">@lang('parking::app.parkir.addParkir')</h4>
    </div>
    <!-- HEADING END -->
    <hr class="m-0 border-top-grey">
    <!-- FORM START -->
    <x-form class="c-inv-form" id="saveInvoiceForm">
        <div class="row px-lg-4 px-md-4 px-3 py-3">
            <div class="col-md-5">
                <x-forms.text fieldId="name" :fieldLabel="__('parking::app.menu.resident')" fieldName="name" fieldRequired="true" :fieldPlaceholder="__('')">
                </x-forms.text>
            </div>
            <div class="col-md-4">
                <x-forms.number fieldId="no_hp" :fieldLabel="__('parking::app.menu.noHP')" fieldName="no_hp" fieldRequired="true"
                    :fieldPlaceholder="__('')">
                    </x-forms.text>
            </div>
            <div class="col-md-3">
                <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('parking::app.menu.unit')" fieldName="parent_label">
                </x-forms.label>
                <select class="form-control select-picker" name="unit_id" id="unit_id" data-live-search="true">
                    <option value="">--</option>
                    @foreach ($unit as $items)
                        <option value="{{ $items->id }}">{{ $items->unit_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('parking::app.menu.status')" fieldName="parent_label"
                    fieldRequired="true">
                </x-forms.label>
                <x-forms.input-group>
                    <select class="form-control select-picker" name="status" id="status">
                        <option value="">--</option>
                        <option value="pemilik-penyewa">Pemilik/ Penyewa</option>
                        <option value="karyawan-tenan">Karyawan Tenant</option>
                        <option value="karyawan-pengelola">Karyawan Pengelola</option>
                        <option value="karyawan-outsourching">Karyawan Outsourching</option>
                    </select>
                </x-forms.input-group>
            </div>
            <div class="col-md-3">
                <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('parking::app.menu.reqType')" fieldName="parent_label"
                    fieldRequired="true">
                </x-forms.label>
                <x-forms.input-group>
                    <select class="form-control select-picker" name="request_type" id="request_type">
                        <option value="">--</option>
                        <option value="daftar-baru">Daftar Baru</option>
                        <option value="perpanjangan">Perpanjangan</option>
                        <option value="ganti-no-plat">Ganti No. Plat</option>
                        <option value="kartu-hilang">Kartu Hilang</option>
                        <option value="lain-lain">Lain-lain</option>
                    </select>
                </x-forms.input-group>
            </div>
            <div class="col-md-6">
                <x-forms.text fieldId="company_name" :fieldLabel="__('parking::app.menu.companyName')" fieldName="company_name" fieldRequired="true"
                    :fieldPlaceholder="__('')">
                </x-forms.text>
            </div>
        </div>
        <!-- CLIENT, PROJECT, GST, BILLING, SHIPPING ADDRESS END -->
        <hr class="m-0 border-top-grey">

        <div id="sortable">
            <!-- DESKTOP DESCRIPTION TABLE START -->
            <div class="d-flex px-4 mt-2 c-inv-desc item-row">
                <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                    <table width="100%">
                        <tbody>
                            <tr class="text-dark-grey font-weight-bold f-14">
                                <td width="20%" class="border-0" align="left" id="type">
                                    @lang('parking::app.menu.jenisKendaraan')</td>
                                <td width="20%" class="border-0" align="left" id="type">
                                    @lang('parking::app.menu.jumlahPeriode')</td>
                                <td width="20%" class="border-0" align="left" id="type">
                                    @lang('parking::app.menu.platLama')</td>
                                <td width="20%" class="border-0" align="left">
                                    @lang('parking::app.menu.platBaru')</td>
                                <td width="20%" class="border-0" align="left">
                                    @lang('parking::app.menu.biaya')</td>
                            </tr>
                            <tr class="border">
                                <td>
                                    <div class="select-others height-35 rounded border-0">
                                        <select class="form-control select-picker" name="jenis_kendaraan[]">
                                            <option value="">--</option>
                                            <option value="motor">Motor</option>
                                            <option value="mobil">Mobil</option>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control f-14 height-35 rounded w-100 item_name"
                                        name="item_name[]">
                                </td>
                                <td>
                                    <input type="text" class="form-control f-14 height-35 rounded w-100"
                                        name="no_plat_lama[]">
                                </td>
                                <td>
                                    <input type="text" class="form-control f-14 height-35 rounded w-100"
                                        name="no_plat_baru[]">
                                </td>
                                <td>
                                    <input type="number" min="1"
                                        class="form-control f-14 height-35 rounded w-100 text-right" value="0"
                                        name="cost_per_item[]">
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <a href="javascript:;" class="d-flex align-items-center justify-content-center ml-3 remove-item"><i
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
        <!--  ADD ITEM END-->
        <hr class="m-0 border-top-grey">

        <!-- TOTAL START -->
        <div class="d-flex px-lg-4 px-md-4 px-3 pb-3 c-inv-total">
            <table width="100%" class="text-right f-14 text-capitalize">
                <tbody>
                    <tr>
                        <td width="60%" class="border-0 d-lg-table d-md-table d-none"></td>
                        <td width="40%" class="p-0 border-0 c-inv-total-right">
                            <table width="100%">
                                <tbody>
                                    <tr class="bg-amt-grey">
                                        <td colspan="1" class="border-top-2 text-dark-grey">
                                            <b>@lang('modules.invoices.subTotal')</b>
                                        </td>
                                        <td width="50%" class="border-top-0">
                                            <input type="text"
                                                class="form-control-plaintext f-14 height-35 rounded w-100 text-right"
                                                value="0" name="total_kredit" id="total_kredit"
                                                class="form-control" readonly>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- TOTAL END -->

        <!-- CANCEL SAVE START -->
        <x-form-actions class="c-inv-btns d-block d-lg-flex d-md-flex">
            <x-forms.button-primary data-type="save" class="save-form mr-3" icon="check">@lang('app.save') Request
                Form
            </x-forms.button-primary>

            <x-forms.button-cancel :link="route('parking.index')" class="border-0 ">@lang('app.cancel')
            </x-forms.button-cancel>
        </x-form-actions>
        <!-- CANCEL SAVE END -->

    </x-form>
    <!-- FORM END -->
</div>
<script>
    $(document).ready(function() {
        $(document).on('click', '#add-item', function() {
            var i = $(document).find('.item_name').length;
            var item = `
            <div class="d-flex px-4 c-inv-desc item-row">
                <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                <table width="100%">
                    <tbody>
                        <tr class="border">
                            <td width="20%">
                                <div class="select-others height-35 rounded border-0">
                                    <select class="form-control select-picker  height-35 f-14" name="jenis_kendaraan[]">
                                        <option value="">--</option>
                                        <option value="motor">Motor</option>
                                        <option value="mobil">Mobil</option>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <input type="number" class="form-control f-14 height-35 rounded w-100 item_name"
                                    name="item_name[]">
                            </td>
                            <td>
                                <input type="text" class="form-control f-14 height-35 rounded w-100"
                                    name="no_plat_lama[]">
                            </td>
                            <td>
                                <input type="text" class="form-control f-14 height-35 rounded w-100"
                                    name="no_plat_baru[]">
                            </td>
                            <td>
                                <input type="number" min="1"
                                    class="form-control f-14 height-35 rounded w-100 text-right" value="0"
                                    name="cost_per_item[]">
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
                <a href="javascript:;" class="d-flex align-items-center justify-content-center ml-3 remove-item"><i class="fa fa-times-circle f-20 text-lightest"></i></a>
            </div>`;
            $(item).hide().appendTo("#sortable").fadeIn(500);
            $('#multiselect' + i).selectpicker();
        });

        $(document).on('keyup', 'input[name^="cost_per_item"]', function() {
            var total = 0;
            $('input[name^="cost_per_item"]').each(function() {
                total += parseInt($(this).val());
            });
            $('#total_kredit').val(total);
        });

        $('#saveInvoiceForm').on('click', '.remove-item', function() {
            $(this).closest('.item-row').fadeOut(300, function() {
                $(this).remove();
                calculateTotal();
            });
        });

        $('.save-form').click(function() {
            var type = $(this).data('type');
            var jumlah = $('#jumlah_total').html();

            if (KTUtil.isMobileDevice()) {
                $('.desktop-description').remove();
            } else {
                $('.mobile-description').remove();
            }

            $.easyAjax({
                url: "{{ route('parking.store') }}" + "?type=" + type,
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


        calculateTotal();
        init(RIGHT_MODAL);
    });
</script>
