@extends('layouts.app')

@section('content')

    <div class="content-wrapper">
        @php
            $addProductPermission = user()->permission('add_parking');
        @endphp
        <div class="bg-white rounded b-shadow-4 create-inv">
            <!-- HEADING START -->
            <div class="px-lg-4 px-md-4 px-3 py-3">
                <h4 class="mb-0 f-21 font-weight-normal text-capitalize">@lang('parking::app.parkir.editParkir')</h4>
            </div>
            <!-- HEADING END -->
            <hr class="m-0 border-top-grey">
            <!-- FORM START -->
            <x-form class="c-inv-form" id="saveInvoiceForm">
                @method('PUT')
                <div class="row px-lg-4 px-md-4 px-3 py-3">
                    <div class="col-md-5">
                        <x-forms.text fieldId="name" :fieldLabel="__('parking::app.menu.resident')" fieldName="name" fieldRequired="true"
                            :fieldValue="$parkir->name">
                        </x-forms.text>
                    </div>
                    <div class="col-md-4">
                        <x-forms.number fieldId="no_hp" :fieldLabel="__('parking::app.menu.noHP')" fieldName="no_hp" fieldRequired="true"
                            :fieldValue="$parkir->no_hp">
                            </x-forms.text>
                    </div>
                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('parking::app.menu.unit')" fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="unit_id" id="unit_id" data-live-search="true">
                            <option value="">--</option>
                            @foreach ($unit as $items)
                                <option @if ($parkir->unit_id == $items->id) selected @endif value="{{ $items->id }}">
                                    {{ $items->unit_name }}</option>
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
                                <option @if ('pemilik-penyewa' == $parkir->status) selected @endif value="pemilik-penyewa">Pemilik/
                                    Penyewa</option>
                                <option @if ('karyawan-tenant' == $parkir->status) selected @endif value="karyawan-tenant">Karyawan
                                    Tenant</option>
                                <option @if ('karyawan-pengelola' == $parkir->status) selected @endif value="karyawan-pengelola">
                                    Karyawan Pengelola</option>
                                <option @if ('karyawan-outsourching' == $parkir->status) selected @endif value="karyawan-outsourching">
                                    Karyawan Outsourching</option>
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
                                <option @if ('daftar-baru' == $parkir->request) selected @endif value="daftar-baru">Daftar Baru
                                </option>
                                <option @if ('perpanjangan' == $parkir->request) selected @endif value="perpanjangan">Perpanjangan
                                </option>
                                <option @if ('ganti-no-plat' == $parkir->request) selected @endif value="ganti-no-plat">Ganti No.
                                    Plat</option>
                                <option @if ('kartu-hilang' == $parkir->request) selected @endif value="kartu-hilang">Kartu Hilang
                                </option>
                                <option @if ('lain-lain' == $parkir->request) selected @endif value="lain-lain">Lain-lain
                                </option>
                            </select>
                        </x-forms.input-group>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="company_name" :fieldLabel="__('parking::app.menu.companyName')" fieldName="company_name" fieldRequired="true"
                            :fieldValue="$parkir->company_name">
                        </x-forms.text>
                    </div>
                </div>
                <!-- CLIENT, PROJECT, GST, BILLING, SHIPPING ADDRESS END -->
                <hr class="m-0 border-top-grey">

                <div id="sortable">
                    @if (isset($parkir))
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
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @foreach ($parkir->items as $key => $item)
                            <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                            <!-- DESKTOP DESCRIPTION TABLE START -->
                            <div class="d-flex px-4 c-inv-desc item-row">
                                <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                                    <table width="100%">
                                        <tbody>
                                            <tr class="border">
                                                <td width="20%">
                                                    <div class="select-others height-35 rounded border-0">
                                                        <select class="form-control select-picker" name="jenis_kendaraan[]">
                                                            <option value="">--</option>
                                                            <option @if ('motor' == $item->jenis_kendaraan) selected @endif value="motor">Motor</option>
                                                            <option @if ('mobil' == $item->jenis_kendaraan) selected @endif value="mobil">Mobil</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control f-14 height-35 rounded w-100 item_name"
                                                        value="{{ $item->jumlah_periode }}" name="item_name[]">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control f-14 height-35 rounded w-100"
                                                        value="{{ $item->no_plat_lama }}" name="no_plat_lama[]">
                                                </td>
                                                <td>
                                                    <input type="text"
                                                        class="form-control f-14 height-35 rounded w-100"
                                                        value="{{ $item->no_plat_baru }}" name="no_plat_baru[]">
                                                </td>
                                                <td>
                                                    <input type="number" min="1"
                                                        class="form-control f-14 height-35 rounded w-100 text-right"
                                                        value="{{ $item->biaya }}" name="cost_per_item[]">
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
                        @endforeach
                    @else
                        <!-- DESKTOP DESCRIPTION TABLE START -->
                        <div class="d-flex px-4 py-3 c-inv-desc item-row">
                            <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                                <table width="100%">
                                    <tbody>
                                        <tr class="text-dark-grey font-weight-bold f-14">
                                            <td width="50%" class="border-0 inv-desc-mbl btlr">@lang('accountings::app.menu.coa')</td>
                                            <td width="25%" class="border-0" align="right" id="type">
                                                @lang('accountings::app.menu.debit')</td>
                                            <td width="25%" class="border-0" align="right">
                                                @lang('accountings::app.menu.credit')
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0">
                                                <div class="select-others height-35 rounded border-0">
                                                    <select class="form-control select-picker" name="taxes[]">
                                                        {{-- @foreach ($coa as $acc_coa)
                                                            <option value="{{ $acc_coa->id }}">
                                                                {{ $acc_coa->coa }}
                                                            </option>
                                                        @endforeach --}}
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="border-bottom-0">
                                                <input type="number" min="1"
                                                    class="form-control f-14 border-0 w-100 text-right item_name"
                                                    name="item_name[]">
                                            </td>
                                            <td class="border-bottom-0">
                                                <input type="number" min="1"
                                                    class="f-14 border-0 w-100 text-right cost_per_item form-control"
                                                    name="cost_per_item[]">
                                            </td>
                                        </tr>
                                        <tr class="d-none d-md-table-row d-lg-table-row">
                                            <td colspan="3" class="dash-border-top bblr border">
                                                <textarea class="f-14 border p-3 rounded w-100 desktop-description form-control" name="item_summary[]"
                                                    placeholder="@lang('placeholders.invoices.description')"></textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <a href="javascript:;"
                                    class="d-flex align-items-center justify-content-center ml-3 remove-item"><i
                                        class="fa fa-times-circle f-20 text-lightest"></i></a>
                            </div>
                        </div>
                    @endif



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
                    <x-forms.button-primary data-type="save" class="save-form mr-3" icon="check">@lang('app.save')
                        Request Form
                    </x-forms.button-primary>

                    <x-forms.button-cancel :link="route('parking.index')" class="border-0 ">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
                <!-- CANCEL SAVE END -->

            </x-form>
            <!-- FORM END -->
        </div>
        <!-- CREATE INVOICE END -->
    </div>

@endsection

@push('scripts')
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
            });

            $(document).ready(function() {
                // Hitung total saat memuat data ke dalam elemen input
                var totalDebit = 0;
                $('input[name^="item_name"]').each(function() {
                    totalDebit += parseInt($(this).val());
                });
                $('#total_debit').val(totalDebit);
                var totalKredit = 0;
                $('input[name^="cost_per_item"]').each(function() {
                    totalKredit += parseInt($(this).val());
                });
                $('#total_kredit').val(totalKredit);

                // Jalankan hitungSelisih() setelah kedua total dihitung
                hitungSelisih();
            });

            $(document).on('keyup', 'input[name^="item_name"]', function() {
                var total = 0;
                $('input[name^="item_name"]').each(function() {
                    total += parseInt($(this).val());
                });
                $('#total_debit').val(total);
                hitungSelisih();
            });

            $(document).on('keyup', 'input[name^="cost_per_item"]', function() {
                var total = 0;
                $('input[name^="cost_per_item"]').each(function() {
                    total += parseInt($(this).val());
                });
                $('#total_kredit').val(total);
                hitungSelisih();
            });

            function hitungSelisih() {
                // Ambil nilai total debit dan total kredit dari elemen input
                const totalDebit = Number($('#total_debit').val());
                const totalKredit = Number($('#total_kredit').val());

                // Hitung selisih antara total debit dan total kredit
                const selisih = totalDebit - totalKredit;

                // Tampilkan hasil selisih di dalam elemen span
                const totalSpan = document.querySelector('span.jumlah');
                totalSpan.textContent = selisih;
            }

            $('#saveInvoiceForm').on('click', '.remove-item', function() {
                $(this).closest('.item-row').fadeOut(300, function() {
                    $(this).remove();
                });
            });

            $('.save-form').click(function() {
                $.easyAjax({
                    url: "{{ route('parking.update', $parkir->id) }}" + "?type=" + type,
                    container: '#saveInvoiceForm',
                    type: "POST",
                    blockUI: true,
                    redirect: true,
                    file: true, // Commented so that we dot get error of Input variables exceeded 1000
                    data: $('#saveInvoiceForm').serialize(),
                })
            });

            $('#saveScheduleForm').on('click', '.remove-item', function() {
                $(this).closest('.item-row').fadeOut(300, function() {
                    $(this).remove();
                    $('select.customSequence').each(function(index) {
                        $(this).attr('name', 'taxes[' + index + '][]');
                        $(this).attr('id', 'multiselect' + index + '');
                    });
                    calculateTotal();
                });
            });

            calculateTotal();
            init(RIGHT_MODAL);
        });
    </script>
@endpush
