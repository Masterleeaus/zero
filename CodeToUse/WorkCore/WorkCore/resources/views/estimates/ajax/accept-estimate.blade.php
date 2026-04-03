<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('modules.quotes.cpatureAndConfirmation')</h5>
    <button type="button"  class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">

    <x-form id="acceptEstimate">
        <div class="row">
            <div class="col-md-4">
                <x-forms.text fieldId="first_name" :fieldLabel="__('modules.quotes.firstName')"
                    fieldName="first_name" fieldRequired="true">
                </x-forms.text>
            </div>
            <div class="col-md-4">
                <x-forms.text fieldId="last_name" :fieldLabel="__('modules.quotes.lastName')" fieldName="last_name"
                    fieldRequired="true">
                </x-forms.text>
            </div>
            <div class="col-md-4">
                <x-forms.text fieldId="email" :fieldLabel="__('app.email')" fieldName="email" fieldRequired="true">
                </x-forms.text>
            </div>
            <div class="col-md-4">
                <x-forms.text fieldId="place" :fieldLabel="__('app.place')" fieldName="place" fieldRequired="true">
                </x-forms.text>
            </div>
            <div class="col-sm-12 bg-grey p-4 signature">
                <x-forms.label fieldId="signature-pad" fieldRequired="true" :fieldLabel="__('modules.quotes.signature')" />
                <div class="signature_wrap wrapper border-0 form-control">
                    <canvas id="signature-pad" class="signature-pad rounded" width=400 height=150></canvas>
                </div>
            </div>
            <div class="col-sm-12 p-4 d-none upload-image">
                <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.quotes.signature')" fieldName="image"
                    fieldId="image" :popover="__('team chat.fileFormat.ImageFile')" fieldRequired="true" />
            </div>
            <div class="col-sm-12 mt-3">
                <x-forms.button-secondary id="undo-signature" class="signature">@lang('modules.quotes.undo')</x-forms.button-secondary>
                <x-forms.button-secondary class="ml-2 signature" id="clear-signature">@lang('modules.quotes.clear')</x-forms.button-secondary>
                <x-forms.button-secondary class="ml-2 " id="toggle-pad-uploader">@lang('modules.quotes.uploadSignature')
                </x-forms.button-secondary>
            </div>

        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-signature" icon="check">@lang('app.sign')</x-forms.button-primary>
</div>

<script>
    $(window).on('load', function() {
        $('.dropify').dropify();
    });
</script>

