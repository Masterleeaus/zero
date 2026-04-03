<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('security::app.wp.validate')</h4>
                <div class="row p-20">
                    <div class="col-md-2">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.date')"
                            ></x-forms.label>
                        <div class="input-group border-bottom">
                            <p class="mb-0">{{ $wp->date }}</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.companyName')"
                            ></x-forms.label>
                        <div class="input-group border-bottom">
                            <p class="mb-0">{{ $wp->company_name }}</p>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.companyAddress')"
                            ></x-forms.label>
                        <div class="input-group border-bottom">
                            <p class="mb-0">{{ $wp->company_address }}</p>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.noHP')"
                            ></x-forms.label>
                        <div class="input-group border-bottom">
                            <p class="mb-0">{{ $wp->phone }}</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.projectManager')"
                            ></x-forms.label>
                        <div class="input-group border-bottom">
                            <p class="mb-0">{{ $wp->project_manj }}</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.siteCoordinator')"
                            ></x-forms.label>
                        <div class="input-group border-bottom">
                            <p class="mb-0">{{ $wp->site_coor }}</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.workType')"
                            ></x-forms.label>
                        <div class="input-group border-bottom">
                            <p class="mb-0">{{ ucwords($wp->jenis_pekerjaan) }}</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.scopeOfWork')"
                            ></x-forms.label>
                        <div class="input-group border-bottom">
                            <p class="mb-0">{{ ucwords($wp->lingkup_pekerjaan) }}</p>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.unit')"
                            ></x-forms.label>
                        <div class="input-group border-bottom">
                            <p class="mb-0">{{ ucwords($wp->unit->unit_name) }}</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.dateStart')"
                            ></x-forms.label>
                        <div class="input-group border-bottom">
                            <p class="mb-0">{{ $wp->date_start }}</p>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.dateEnd')"
                            ></x-forms.label>
                        <div class="input-group border-bottom">
                            <p class="mb-0">{{ $wp->date_end }}</p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.workDescription')"
                            ></x-forms.label>
                        <div class="form-group border p-2">
                            <p class="mb-0">{{ $wp->detail_pekerjaan }}</p>
                        </div>
                    </div>
                </div>
                <hr class="">
                <div class="row px-3">
                    <div class="col-lg-12">
                        <x-forms.label class="my-3" fieldId="description-text" :fieldLabel="__('security::app.menu.remark')"
                        fieldRequired="true" >
                        </x-forms.label>
                        <textarea class="form-control" name="remark" rows="2"></textarea>
                    </div>
                    <div class="col-lg-12">
                        <x-forms.file allowedFileExtensions="png jpg jpeg svg" class="mr-0 mr-lg-2 mr-md-2 cropper"
                            :fieldLabel="__('assets::app.menu.image')" fieldName="image" fieldId="image" fieldHeight="120" />
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-leave-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('security-workpermit.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>


<script>
    $(document).ready(function() {
        $('#save-leave-form').click(function() {
            const url = "{{ route('security-workpermit.validated', $wp->id) }}";
            $.easyAjax({
                url: url,
                container: '#save-lead-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
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
