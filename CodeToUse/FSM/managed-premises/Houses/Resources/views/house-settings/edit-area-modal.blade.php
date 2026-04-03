<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('app.update') @lang('houses::modules.house.area')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="editArea" method="PUT" class="ajax-form">
            <div class="form-body">
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text fieldId="area_code" :fieldLabel="__('houses::modules.house.areacode')"
                            fieldName="area_code" fieldRequired="true" fieldPlaceholder="e.g. 01, 02, etc." :fieldValue="$area->area_code">
                        </x-forms.text>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <x-forms.text fieldId="area_name" :fieldLabel="__('houses::modules.house.areaName')"
                            fieldName="area_name" fieldRequired="true" fieldPlaceholder="e.g. First Area, Scond Area, etc." :fieldValue="$area->area_name">
                        </x-forms.text>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-house-area" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    // save house channnel
    $('#save-house-area').click(function () {
        $.easyAjax({
            url: "{{route('areas.update', $area->id)}}",
            container: '#editArea',
            type: "POST",
            blockUI: true,
            data: $('#editArea').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            }
        })
    });
</script>
