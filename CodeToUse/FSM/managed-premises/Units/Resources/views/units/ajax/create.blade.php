<div class="row">
    <div class="col-sm-12">
        <x-form id="save-unit-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.add') @lang('units::app.menu.unit')</h4>
                <div class="row p-20">
                    <div class="col-md-3">
                        <x-forms.text fieldId="unit_code" :fieldLabel="__('units::modules.unit.unitCode')" fieldName="unit_code"
                                      fieldRequired="true" :fieldPlaceholder="__('01-01')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-3">
                        <x-forms.text fieldId="unit_name" :fieldLabel="__('app.name')" fieldName="unit_name"
                                      fieldRequired="true" :fieldPlaceholder="__('Unit 01-01')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('units::app.menu.tower')"
                                       fieldName="parent_label">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="tower_id" id="tower_id"
                                    data-live-search="true">
                                <option value="">--</option>
                                @foreach($towers as $tower)
                                    <option value="{{ $tower->id }}">{{ $tower->tower_name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('units::app.menu.floor')"
                                       fieldName="parent_label">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="floor_id" id="floor_id"
                                    data-live-search="true">
                                <option value="">--</option>
                                @foreach($floors as $floor)
                                    <option value="{{ $floor->id }}">{{ $floor->floor_name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('units::app.menu.typeunit')"
                                       fieldName="parent_label">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="typeunit_id" id="typeunit_id"
                                    data-live-search="true">
                                <option value="">--</option>
                                @foreach($typeunits as $typeunit)
                                    <option value="{{ $typeunit->id }}">{{ $typeunit->typeunit_name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                    <div class="col-md-3">
                        <x-forms.text fieldId="luas" :fieldLabel="__('units::modules.unit.luas')" fieldName="luas"
                                        fieldRequired="true" :fieldPlaceholder="__('')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="address" :fieldLabel="__('app.address')" fieldName="address"
                                      fieldRequired="true">
                        </x-forms.text>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-unit-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('units.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function () {

        $('#save-unit-form').click(function () {

            const url = "{{ route('units.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-unit-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-unit-form",
                data: $('#save-unit-data-form').serialize(),
                success: function (response) {
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
