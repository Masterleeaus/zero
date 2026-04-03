<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-data-form" method="put">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.edit') @lang('app.menu.unit')</h4>
                <div class="row p-20">
                    <div class="col-md-12">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('units::modules.unit.user')" fieldName="parent_label">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="user_id" id="user_id"
                                data-live-search="true" disabled>
                                <option value="">No Items Selected</option>
                                @foreach ($users as $user)
                                    <option @if ($user->id == $user_id) selected @endif
                                        value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                </div>

                <!-- CLIENT, PROJECT, GST, BILLING, SHIPPING ADDRESS END -->
                <hr class="m-0 border-top-grey">
                <!--  ADD ITEM START-->
                <div class="row px-lg-4 px-md-4 px-3 pt-0 mt-3 mb-3">
                    <div class="col-md-12">
                        <a class="f-15 f-w-500 mr-4" href="javascript:;" id="add-item">
                            <i class="icons icon-plus font-weight-bold mr-1"></i>@lang('app.add') @lang('units::app.menu.unit')
                        </a>
                    </div>
                </div>

                <div id="sortable" class="mb-3">
                    @foreach ($users_units as $key => $item)
                        <!-- DESKTOP DESCRIPTION TABLE START -->
                        <div class="d-flex px-4 c-inv-desc item-row">
                            <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                                <table width="100%">
                                    <tbody>
                                        <tr class="text-dark-grey font-weight-bold f-14">
                                            <td width="98%" class="border">@lang('units::app.menu.unit')</td>
                                            <td width="2%" class="border-0" align="right"></td>
                                        </tr>
                                        <tr>
                                            <td width="98%">
                                                <div class="select-others height-35 rounded border-0">
                                                    <x-forms.input-group>
                                                        <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                                                        <select
                                                            class="form-control select-picker height-35 f-14 unit_id"
                                                            id="unit_id" name="unit_id[]"
                                                            data-row-id="{{ $key }}">
                                                            <option value="">No Items Selected</option>
                                                            @foreach ($units as $unit)
                                                                <option
                                                                    @if ($unit->id == $item->unit_id) selected @endif
                                                                    value="{{ $unit->id }}">{{ $unit->unit_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </x-forms.input-group>
                                                </div>
                                            </td>
                                            <td class="border-0">
                                                <a href="javascript:;"
                                                    class="d-flex align-items-center justify-content-center remove-item"><i
                                                        class="fa fa-times-circle f-20 text-lightest"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-leave-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('units-configuration.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $(document).on('click', '#add-item', function() {
            var i = $(document).find('.unit_id').length;
            var item =
                `<div class="d-flex px-4 c-inv-desc item-row">
                    <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block">
                        <table width="100%">
                            <tbody>
                                <tr>
                                    <td width="98%">
                                        <div class="select-others height-35 rounded border-0">
                                            <x-forms.input-group>
                                                <select class="form-control select-picker height-35 f-14 unit_id" id="unit_id"
                                                    name="unit_id[]" data-row-id="${i}">
                                                    <option value="">No Items Selected</option>
                                                    @foreach ($units as $unit)
                                                        <option value="{{ $unit->id }}">{{ $unit->unit_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </x-forms.input-group>
                                        </div>
                                    </td>
                                    <td class="border-0">
                                        <a href="javascript:;" class="d-flex align-items-center justify-content-center remove-item"><i
                                                class="fa fa-times-circle f-20 text-lightest"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>`;
            $(item).hide().appendTo("#sortable").fadeIn(500);
            $('.select-picker').selectpicker();
        });

        $('#save-lead-data-form').on('click', '.remove-item', function() {
            $(this).closest('.item-row').fadeOut(300, function() {
                $(this).remove();
                calculateTotal();
            });
        });

        let selectedItems = [];

        function handleItemSelection(selectElement) {
            const selectedItem = selectElement.val();
            const selectId = selectElement.data('row-id');

            if (selectedItem !== '') {
                if (!selectedItems.hasOwnProperty(selectedItem)) {
                    selectedItems[selectedItem] = selectId;
                } else if (selectedItems[selectedItem] !== selectId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Item sudah dipilih sebelumnya.'
                    });
                    selectElement.val('');
                    selectElement.selectpicker('refresh');
                }
            }
        }

        $(document).on('change', 'select[name="unit_id[]"]', function() {
            const selectElement = $(this);
            handleItemSelection(selectElement);
        });

        $('#save-leave-form').click(function() {
            const url = "{{ route('units-configuration.update', $user_id) }}";
            $.easyAjax({
                url: url,
                container: '#save-lead-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-leave-form",
                data: $('#save-lead-data-form').serialize(),
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
