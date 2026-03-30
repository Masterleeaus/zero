<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-data-form" method="put">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.edit') @lang('app.menu.house')</h4>
                    <div class="row p-20">
                        <div class="col-md-3">
                            <x-forms.text fieldId="house_code" :fieldLabel="__('houses::modules.house.houseCode')" fieldName="house_code"
                                :fieldValue="$house->house_code" fieldRequired="true" :fieldPlaceholder="__('01-01')">
                            </x-forms.text>
                        </div>
                        <div class="col-md-3">
                            <x-forms.text fieldId="house_name" :fieldLabel="__('app.name')" fieldName="house_name"
                                :fieldValue="$house->house_name" fieldRequired="true" :fieldPlaceholder="__('House 01-01')">
                            </x-forms.text>
                        </div>
                        <div class="col-md-3">
                            <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('houses::app.menu.tower')"
                                           fieldName="parent_label">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="tower_id" id="tower_id"
                                        data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($towers as $tower)
                                    <option @if ($tower->id == $house->tower_id) selected
                                            @endif value="{{ $tower->id }}">
                                        {{ $tower->tower_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                        <div class="col-md-3">
                            <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('houses::app.menu.area')"
                                           fieldName="parent_label">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="area_id" id="area_id"
                                        data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($areas as $area)
                                    <option @if ($area->id == $house->area_id) selected
                                            @endif value="{{ $area->id }}">
                                        {{ $area->area_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                    </div>
                    <div class="row p-20">
                        <div class="col-md-3">
                            <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('houses::app.menu.typehouse')"
                                           fieldName="parent_label">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="typehouse_id" id="typehouse_id"
                                        data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($typehouses as $typehouse)
                                    <option @if ($typehouse->id == $house->typehouse_id) selected
                                            @endif value="{{ $typehouse->id }}">
                                        {{ $typehouse->typehouse_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                        <div class="col-md-3">
                            <x-forms.text fieldId="luas" :fieldLabel="__('houses::modules.house.luas')" fieldName="luas"
                                :fieldValue="$house->luas" fieldRequired="true" :fieldPlaceholder="__('')">
                            </x-forms.text>
                        </div>
                    </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-leave-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('houses.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script>
    $(document).ready(function() {

        $('#save-leave-form').click(function() {

            const url = "{{ route('houses.update', $house->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-lead-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-leave-form",
                data: $('#save-lead-data-form').serialize(),
                success: function(response) {
                    window.location.href = response.redirectUrl;
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
