<style>
    .mt{
        margin-top: -4px;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-zone-data-form" method="PUT">
            <div class="add-customer bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.menu.editDepartment')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="team_name" :fieldLabel="__('app.name')" fieldName="team_name"
                            fieldRequired="true" fieldValue="{{ $zone->team_name }}">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.label class="my-3 mt-2" fieldId="parent_label" :fieldLabel="__('app.parentId')" fieldName="parent_label">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker mt" name="parent_id" id="parent_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach($zones as $item)
                                        <option value="{{ $item->id }}" @if($zone->parent_id == $item->id) selected @endif>{{ $item->team_name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-zone-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('zones.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        $('#save-zone-form').click(function() {

            const url = "{{ route('zones.update', $zone->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-zone-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-zone-form",
                data: $('#save-zone-data-form').serialize(),
                success: function(response) {
                    window.location.href = response.redirectUrl;
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
