<div class="row">
    <div class="col-sm-12">
        <x-form class="c-inv-form" id="saveInvoiceForm">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('traccesscard::app.card.addCard')</h4>
                <div class="row p-20">
                    <div class="col-md-12">
                        <div class="border p-2">
                            <x-forms.label fieldId="parent_label" :fieldLabel="__('traccesscard::app.menu.notes')" fieldName="parent_label">
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
                        <x-forms.label class=" mt-3" fieldId="date" :fieldLabel="__('traccesscard::app.menu.date')"
                            fieldRequired="true"></x-forms.label>
                        <div class="input-group">
                            <input type="text" id="date" name="date"
                                class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                placeholder="@lang('placeholders.date')"
                                value="{{ now(company()->timezone)->translatedFormat('d-m-Y') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('traccesscard::app.menu.unit')" fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="unit_id" id="unit_id"
                            data-live-search="true">
                            @foreach ($units as $items)
                                <option value="{{ $items->id }}">{{ $items->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="name" :fieldLabel="__('traccesscard::app.menu.resident')" fieldName="name" fieldRequired="true"
                            :fieldPlaceholder="__('')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="no_hp" :fieldLabel="__('traccesscard::app.menu.noHP')"
                            fieldRequired="true"></x-forms.label>
                        <div class="input-group">
                            <input type="number" id="no_hp" name="no_hp"
                                class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                placeholder="">
                        </div>
                    </div>

                    @if (!in_array('client', user_roles()))
                        <div class="col-md-12">
                            <x-forms.label class=" mt-3" fieldId="charge_card" :fieldLabel="__('traccesscard::app.menu.biaya')"
                                fieldRequired="true"></x-forms.label>
                            <div class="input-group">
                                <input type="number" id="charge_card" name="charge_card"
                                    class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                    placeholder="">
                            </div>
                        </div>
                    @endif
                </div>

                <!-- CLIENT, PROJECT, GST, BILLING, SHIPPING ADDRESS END -->
                <hr class="m-0 border-top-grey">

                <div id="sortable">
                    <!-- DESKTOP DESCRIPTION TABLE START -->
                    <div class="d-flex px-4 mt-4 c-inv-desc">
                        <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                            <table width="100%">
                                <tbody>
                                    <tr class="text-dark-grey font-weight-bold f-14">
                                        <td width="25%">
                                            @lang('traccesscard::app.menu.name')</td>
                                        <td width="25%">
                                            @lang('traccesscard::app.menu.status')</td>
                                        <td width="45%">
                                            @lang('traccesscard::app.menu.noKartu')</td>
                                        <td width="5%" class="border-0"> </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="d-flex px-4 c-inv-desc item-row">
                        <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                            <table width="100%">
                                <tbody>
                                    <tr class="border">
                                        <td width="25%">
                                            <input type="text" class="form-control f-14 height-35 rounded w-100"
                                                name="name_card[]">
                                        </td>
                                        <td width="25%">
                                            <div class="select-others height-35 rounded border-0">
                                                <select class="form-control select-picker" name="status_card[]">
                                                    @if (in_array('client', user_roles()))
                                                        <option value="pengajuan">Submission</option>
                                                    @else
                                                        <option value="pengajuan">Submission</option>
                                                        <option value="approved">Approved</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </td>
                                        <td width="45%">
                                            @if (in_array('client', user_roles()))
                                                <input type="number"
                                                    class="form-control f-14 height-35 rounded w-100 item_name"
                                                    name="card_number[]" readonly>
                                            @else
                                                <input type="number"
                                                    class="form-control f-14 height-35 rounded w-100 item_name"
                                                    name="card_number[]">
                                            @endif
                                        </td>
                                        <td width="5%" class="border-0">
                                            <a href="javascript:;"
                                                class="d-flex align-items-center justify-content-center ml-3 remove-item"><i
                                                    class="fa fa-times-circle f-20 text-lightest"></i></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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

                <x-form-actions>
                    <x-forms.button-primary class="save-form mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('card-access.index')">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
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

        $(document).on('click', '#add-item', function() {
            var i = $(document).find('.item_name').length;
            var item = `
            <div class="d-flex px-4 c-inv-desc item-row">
                        <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                            <table width="100%">
                                <tbody>
                                    <tr class="border">
                                        <td width="25%">
                                            <input type="text" class="form-control f-14 height-35 rounded w-100"
                                                name="name_card[]">
                                        </td>
                                        <td width="25%">
                                            <div class="select-others height-35 rounded border-0">
                                                <select class="form-control f-14 height-35 select-picker" name="status_card[]">
                                                    @if (in_array('client', user_roles()))
                                                        <option value="pengajuan">Submission</option>
                                                    @else
                                                        <option value="pengajuan">Submission</option>
                                                        <option value="approved">Approved</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </td>
                                        <td width="45%">
                                            @if (in_array('client', user_roles()))
                                                <input type="number"
                                                    class="form-control f-14 height-35 rounded w-100 item_name"
                                                    name="card_number[]" readonly>
                                            @else
                                                <input type="number"
                                                    class="form-control f-14 height-35 rounded w-100 item_name"
                                                    name="card_number[]">
                                            @endif
                                        </td>
                                        <td width="5%" class="border-0">
                                            <a href="javascript:;" class="d-flex align-items-center justify-content-center ml-3 remove-item"><i
                                                class="fa fa-times-circle f-20 text-lightest"></i></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>`;
            $(item).hide().appendTo("#sortable").fadeIn(500);
            $('#multiselect' + i).selectpicker();
        });

        $('#saveInvoiceForm').on('click', '.remove-item', function() {
            $(this).closest('.item-row').fadeOut(300, function() {
                $(this).remove();
            });
        });

        $('.save-form').click(function() {
            const url = "{{ route('card-access.store') }}";
            $.easyAjax({
                url: url,
                container: '#saveInvoiceForm',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#saveInvoiceForm",
                data: $('#saveInvoiceForm').serialize(),
                success: function(response) {
                    if (response.status === 'success') {
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

        init(RIGHT_MODAL);
    });
</script>
