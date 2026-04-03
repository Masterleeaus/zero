<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-service job-data-form">
            <input type="hidden" name="template_id" value="{{ $template->id }}" />
            <div class="add-customer bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.service jobs.taskInfo')</h4>
                <div class="row p-20">
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text :fieldLabel="__('app.title')" fieldName="heading" fieldRequired="true"
                                      fieldId="heading" :fieldPlaceholder="__('placeholders.service job')" />
                    </div>

                    <div class="col-lg-6 col-md-6">
                        <x-forms.label class="my-3" fieldId="category_id"
                                       :fieldLabel="__('modules.service jobs.taskCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="task_category_id"
                                    data-live-search="true" data-size="8">
                                <option value="">--</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>

                            <x-slot name="append">
                                <button id="create_task_category" type="button"
                                        class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                            </x-slot>
                        </x-forms.input-group>
                    </div>

                    <div class="col-lg-6 col-md-6">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="selectAssignee"
                                           :fieldLabel="__('modules.service jobs.assignTo')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control multiple-users" multiple name="user_id[]"
                                        id="selectAssignee" data-live-search="true" data-size="8">
                                    @foreach ($cleaners as $item)
                                        <x-user-option :user="$item" :pill="true" />
                                    @endforeach
                                </select>

                                <x-slot name="append">
                                    <button id="add-cleaner" type="button"
                                            class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                </x-slot>
                            </x-forms.input-group>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <x-forms.select fieldId="priority" :fieldLabel="__('modules.service jobs.priority')"
                                        fieldName="priority">
                            <option value="high">@lang('modules.service jobs.high')</option>
                            <option selected value="medium">@lang('modules.service jobs.medium')</option>
                            <option value="low">@lang('modules.service jobs.low')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="description" :fieldLabel="__('app.description')">
                            </x-forms.label>
                            <div id="description"></div>
                            <textarea name="description" id="description-text" class="d-none"></textarea>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 mt-3">
                        <x-forms.label fieldId="task_labels" :fieldLabel="__('app.label')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="select-picker form-control" multiple name="task_labels[]"
                                    id="task_labels" data-live-search="true" data-size="8">
                                @foreach ($labels as $label)
                                    <option
                                        data-content="<span class='badge badge-secondary' style='background-color: {{ $label->label_color }}'>{{ $label->label_name }}</span>"
                                        value="{{ $label->id }}">
                                        {{ $label->label_name }}</option>
                                @endforeach
                            </select>

                            @if (user()->permission('task_labels') == 'all')
                                <x-slot name="append">
                                    <button id="createTaskLabel" type="button"
                                            class="btn btn-outline-secondary border-grey"
                                            data-toggle="tooltip"
                                            data-original-title="{{ __('modules.taskLabel.addLabel') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                     <div class="col-lg-6 col-md-6">
                            <x-forms.select fieldName="milestone_id" fieldId="milestone-id"
                                            :fieldLabel="__('modules.sites.milestones')">
                                <option value="">--</option>
                                        @foreach ($template->milestones as $item)
                                            <option value="{{ $item->id }}"
                                                     >{{ $item->milestone_title }}</option>
                                        @endforeach
                            </x-forms.select>
                    </div>

                </div>


                <x-form-actions>
                    <x-forms.button-primary class="mr-3" id="save-service job-form" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('site-template.show', $template->id.'?tab=service jobs')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script>
    $(document).ready(function() {

        $("#selectAssignee").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        quillImageLoad('#description');

        $('#save-service job-form').click(function() {
            var note = document.getElementById('description').children[0].innerHTML;
            document.getElementById('description-text').value = note;

            const url = "{{ route('site-template-service job.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-service job-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-service job-form",
                data: $('#save-service job-data-form').serialize(),
                success: function(response) {
                    window.location.href = response.redirectUrl;
                }
            });
        });

        $('#createTaskLabel').click(function () {
            var projectId = $('#project_id').val();
            const url = "{{ route('service job-label.create') }}";
            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        $('#create_task_category').click(function() {
            const url = "{{ route('taskCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#zone-setting').click(function() {
            const url = "{{ route('zones.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#add-cleaner').click(function() {
            $(MODAL_XL).modal('show');

            const url = "{{ route('cleaners.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function(response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });

</script>
