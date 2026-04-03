<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.update') @lang('units::modules.unit.floor')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="editFloor" method="PUT" class="ajax-form">
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text fieldId="floor_code" :fieldLabel="__('units::modules.unit.floorcode')"
                            fieldName="floor_code" fieldRequired="true" fieldPlaceholder="e.g. 01, 02, etc." :fieldValue="$floor->floor_code">
                        </x-forms.text>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text fieldId="floor_name" :fieldLabel="__('units::modules.unit.floorName')"
                            fieldName="floor_name" fieldRequired="true" fieldPlaceholder="e.g. First Floor, Scond Floor, etc." :fieldValue="$floor->floor_name">
                        </x-forms.text>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-unit-floor" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    // save unit channnel
    $('#save-unit-floor').click(function () {
        $.easyAjax({
            url: "{{route('floors.update', $floor->id)}}",
            container: '#editFloor',
            type: "POST",
            blockUI: true,
            data: $('#editFloor').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            }
        })
    });
</script>
