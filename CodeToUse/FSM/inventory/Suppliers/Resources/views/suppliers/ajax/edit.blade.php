<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-data-form" method="put">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.edit') @lang('suppliers::app.menu.supplier')</h4>
                    <div class="row p-20">
                        <div class="col-md-4">
                            <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" :fieldValue="$unit->name">
                            </x-forms.text>
                        </div>
                        <div class="col-md-3">
                            <x-forms.number fieldId="phone" :fieldLabel="__('app.phone')" fieldName="phone" fieldRequired="true" :fieldValue="$unit->phone">
                            </x-forms.number>
                        </div>
                        <div class="col-md-3">
                            <x-forms.number fieldId="fax" :fieldLabel="__('suppliers::app.menu.fax')" fieldName="fax" fieldRequired="true" :fieldValue="$unit->fax">
                            </x-forms.number>
                        </div>
                        <div class="col-md-2">
                            <x-forms.number fieldId="kode_pos" :fieldLabel="__('suppliers::app.menu.kodePos')" fieldName="kode_pos" fieldRequired="true" :fieldValue="$unit->kode_pos">
                            </x-forms.number>
                        </div>
                        <div class="col-md-12">
                            <x-forms.text fieldId="alamat" :fieldLabel="__('suppliers::app.menu.alamat')" fieldName="alamat" fieldRequired="true" :fieldValue="$unit->alamat">
                            </x-forms.text>
                        </div>
                        <div class="col-md-4">
                            <x-forms.text fieldId="contact_person" :fieldLabel="__('suppliers::app.menu.contactPerson')" fieldName="contact_person" fieldRequired="true" :fieldValue="$unit->contact_person">
                            </x-forms.text>
                        </div>
                        <div class="col-md-4">
                            <x-forms.number fieldId="phone_contact_person" :fieldLabel="__('suppliers::app.menu.phoneCP')" fieldName="phone_contact_person" fieldRequired="true" :fieldValue="$unit->phone_contact_person">
                            </x-forms.number>
                        </div>
                        <div class="col-md-4">
                            <x-forms.text fieldId="email" :fieldLabel="__('app.email')" fieldName="email" fieldRequired="true" :fieldValue="$unit->email">
                            </x-forms.text>
                        </div>
                        {{-- <div class="col-md-3">
                            <x-forms.text fieldId="unit_code" :fieldLabel="__('suppliers::modules.unit.unitCode')" fieldName="unit_code"
                                :fieldValue="$unit->unit_code" fieldRequired="true" :fieldPlaceholder="__('01-01')">
                            </x-forms.text>
                        </div> --}}
                        
                    </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-leave-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('suppliers.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script>
    $(document).ready(function() {

        $('#save-leave-form').click(function() {
            const url = "{{ route('suppliers.update', $unit->id) }}";
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
