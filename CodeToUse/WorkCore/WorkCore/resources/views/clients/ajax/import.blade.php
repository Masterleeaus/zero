<div class="row" id="import_table">
    <div class="col-sm-12">
        <x-form id="import-customer-data-form">
            <div class="add-customer bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.importExcelClient')</h4>
                <div class="row py-20">
                    <div class="col-md-12">
                        <x-forms.file :fieldLabel="__('modules.import.file')" fieldName="import_file" fieldId="client_import" />
                    </div>
                    <div class="col-md-12">
                        <x-forms.link-secondary :link="asset('sample-import/customer-sample.xlsx')" icon="download">@lang('app.downloadSampleImport')</x-forms.link-secondary>

                        <x-forms.toggle-switch class="mr-0 mr-lg-12"
                            :fieldLabel="__('modules.import.containsHeadings')"
                            fieldName="heading"
                            fieldId="heading"/>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="import-customer-form" class="mr-3" icon="arrow-right">@lang('app.uploadNext')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('customers.index')" class="border-0">@lang('app.back')
                    </x-forms.button-cancel>

                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>

    $(document).ready(function() {

        $("#client_import").dropify({
            team chat: dropifyMessages
        });

        $('body').on('click', '#import-customer-form', function() {
            const url = "{{ route('customers.import.store') }}";

            $.easyAjax({
                url: url,
                container: '#import-customer-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#import-customer-form",
                file: true,
                data: $('#import-customer-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        $('#import_table').html(response.view);
                    }
                }
            });
        });
    });
</script>
