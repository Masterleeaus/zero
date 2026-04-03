@php
$addProjectCategoryPermission = user()->permission('manage_project_category');
$addClientPermission = user()->permission('add_clients');
$editProjectMemberPermission = user()->permission('edit_project_members');
$addEmployeePermission = user()->permission('add_employees');
$addProjectMemberPermission = user()->permission('add_project_members');
$addProjectMemberPermission = user()->permission('add_project_members');
$createPublicProjectPermission = user()->permission('create_public_project');

@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-site-data-form" method="PUT">
            <div class="add-customer bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.sites.projectInfo')</h4>
                <div class="row p-20">
                    <div class="col-lg-4 col-md-4">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.taskShortCode')"
                            fieldName="project_code" fieldRequired="false" fieldId="project_code"
                            :fieldPlaceholder="__('placeholders.writeshortcode')" :fieldValue="$site->project_short_code" />
                    </div>
                    <div class="col-lg-8 col-md-8">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.sites.projectName')"
                            fieldName="project_name" fieldRequired="true" fieldId="project_name"
                            :fieldValue="$site->project_name" :fieldPlaceholder="__('placeholders.site')" />
                    </div>
                    <input type="hidden" name="project_id" value="{{ $site->id }}">
                    <div class="col-md-6 col-lg-4">
                        <x-forms.datepicker fieldId="start_date" fieldRequired="true"
                            :fieldLabel="__('modules.sites.startDate')" fieldName="start_date"
                            :fieldValue="($site->start_date ? $site->start_date->format(company()->date_format) : '')"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-6 col-lg-4" id="deadlineBox">
                        <x-forms.datepicker fieldId="deadline" fieldRequired="true"
                            :fieldLabel="__('modules.sites.deadline')" fieldName="deadline"
                            :fieldValue="($site->deadline ? $site->deadline->format(company()->date_format) : '')"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="{{ $site->deadline == null ? 'col-md-8' : 'col-md-6 col-lg-4' }}">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="without_deadline"
                                    :checked="($site->deadline == null) ? true : false"
                                    :fieldLabel="__('modules.sites.withoutDeadline')" fieldName="without_deadline" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <x-forms.label class="my-3" fieldId="category_id"
                            :fieldLabel="__('modules.sites.projectCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="project_category_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($categories as $category)
                                    <option @selected($site->category_id == $category->id) value="{{ $category->id }}">
                                        {{ $category->category_name }}</option>
                                @endforeach
                            </select>

                            @if ($addProjectCategoryPermission == 'all' || $addProjectCategoryPermission == 'added')
                                <x-slot name="append">
                                    <button id="addProjectCategory" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{__('modules.projectCategory.addProjectCategory') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    @if (!in_array('customer', user_roles()) && ($editProjectMembersPermission == 'all' || $editPermission == 'all'))
                        <div class="col-md-4">
                            <x-forms.label class="my-3" fieldId="zone" :fieldLabel="__('app.zone')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control multiple-users" multiple name="team_id[]" id="employee_department"
                                    data-live-search="true">
                                    @foreach ($teams as $team)
                                        <option data-content="<span class='badge badge-pill badge-light border p-2'>{{ $team->team_name }}</span>"
                                        @selected(in_array($team->id, $departmentIds)) value="{{ $team->id }}">
                                            {{ $team->team_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                    @endif

                    <div class="col-md-4 @if(!in_array('customers', user_modules())) d-none @endif">

                        <x-forms.input-group>
                            <x-customer-selection-dropdown labelClass="my-3" :customers="$customers" fieldRequired="false"
                                                         :selected="$site->client_id ?? null"/>
{{--                            @if ($addClientPermission == 'all' || $addClientPermission == 'added')--}}
{{--                                <x-slot name="append">--}}
{{--                                    <button id="add-customer" type="button"--}}
{{--                                        class="btn btn-outline-secondary border-grey"--}}
{{--                                        data-toggle="tooltip" data-original-title="{{__('modules.customer.addNewClient') }}">@lang('app.add')</button>--}}
{{--                                </x-slot>--}}
{{--                            @endif--}}
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-12 col-lg-12">
                        <div class="form-group my-3">
                            <x-forms.label class="my-3" fieldId="project_summary"
                                :fieldLabel="__('modules.sites.projectSummary')">
                            </x-forms.label>
                            <div id="project_summary">{!! $site->project_summary !!}</div>
                            <textarea name="project_summary" id="project_summary-text"
                                class="d-none">{!! $site->project_summary !!}</textarea>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('modules.sites.viewPublicGanttChart')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="public_gantt_chart-yes" :fieldLabel="__('app.enable')" fieldName="public_gantt_chart"
                                    fieldValue="enable" :checked="$site->public_gantt_chart == 'enable'"  checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="public_gantt_chart-no" :fieldLabel="__('app.disable')" fieldValue="disable"
                                    fieldName="public_gantt_chart" :checked="$site->public_gantt_chart == 'disable'"></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('app.public') @lang('modules.service jobs.taskBoard')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="public_taskboard-yes" :fieldLabel="__('app.enable')" fieldName="public_taskboard"
                                    fieldValue="enable" :checked="$site->public_taskboard == 'enable'"  checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="public_taskboard-no" :fieldLabel="__('app.disable')" fieldValue="disable"
                                    fieldName="public_taskboard" :checked="$site->public_taskboard == 'disable'"></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('modules.sites.needApproval')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="need_approval_by_admin-yes" :fieldLabel="__('app.enable')" fieldName="need_approval_by_admin"
                                    fieldValue="1" :checked="$site->need_approval_by_admin == '1'"  checked="true">
                                </x-forms.radio>
                                <x-forms.radio fieldId="need_approval_by_admin-no" :fieldLabel="__('app.disable')" fieldValue="0"
                                    fieldName="need_approval_by_admin" :checked="$site->need_approval_by_admin == '0'"></x-forms.radio>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="project_labels" :fieldLabel="__('app.label')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="select-picker form-control" multiple name="project_labels[]"
                                    id="project_labels" data-live-search="true" data-size="8">
                                    @foreach ($projectLabels as $label)
                                        @php
                                            $selected = '';
                                        @endphp

                                        @foreach ($site->label as $item)
                                            @if ($item->label_id == $label->id)
                                                @php
                                                    $selected = 'selected';
                                                @endphp
                                            @endif
                                        @endforeach
                                        <option {{ $selected }}
                                            data-content="<span class='badge badge-secondary' style='background-color: {{ $label->label_color }}'>{{ $label->label_name }}</span>"
                                            value="{{ $label->id }}">{{ $label->label_name }}</option>
                                    @endforeach
                                </select>


                                @if (user()->permission('project_labels') == 'all')
                                    <x-slot name="append">
                                        <button id="createProjectLabel" type="button"
                                            class="btn btn-outline-secondary border-grey"
                                            data-toggle="tooltip" data-original-title="{{ __('modules.projectLabel.addLabel') }}">@lang('app.add')</button>
                                    </x-slot>
                                @endif
                            </x-forms.input-group>
                        </div>
                    </div>

                    @if ($site->public == 1 && $createPublicProjectPermission == 'all')
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="d-flex mt-2">
                                    <x-forms.checkbox fieldId="is_private"
                                        :fieldLabel="__('modules.sites.createPrivateProject')" fieldName="private" />
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($site->public == 0 && $createPublicProjectPermission == 'all')
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="d-flex mt-2">
                                    <x-forms.checkbox fieldId="is_public"
                                        :fieldLabel="__('modules.sites.changeToPublicProject')" fieldName="public" />
                                </div>
                            </div>
                        </div>
                    @endif


                    @if ($editProjectMembersPermission == 'all' || $editPermission == 'all')
                        <div class="col-md-12 @if ($site->public == 1) d-none @endif" id="edit_members">
                           <div class="form-group my-3">

                            @php
                                // Retrieve selected cleaner IDs and their statuses
                                $selectedEmployeeIds = $site->members->pluck('user_id')->toArray();
                                $selectedEmployees = $cleaners->filter(function ($cleaner) use ($selectedEmployeeIds) {
                                    return in_array($cleaner->id, $selectedEmployeeIds);
                                });

                                // Get the active cleaners list
                                $activeEmployees = $cleaners->filter(function ($cleaner) {
                                    return $cleaner->status === 'active';
                                });

                                $employeesToShow = $activeEmployees->merge($selectedEmployees->filter(function ($cleaner) {
                                    return !$cleaner->is_active;
                                }));
                            @endphp
                                <x-forms.label fieldId="selectAssignee"
                                fieldRequired="true" :fieldLabel="__('modules.service jobs.assignTo')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control multiple-users" multiple name="member_id[]"
                                        id="selectEmployee" data-live-search="true" data-size="8">
                                        @foreach ($employeesToShow as $item)
                                            @php
                                                $selected = '';
                                                $isMember = false;
                                            @endphp

                                            @foreach ($site->members as $member)
                                                @if ($member->user->id == $item->id)
                                                    @php
                                                        $selected = 'selected';
                                                        $isMember = true;
                                                    @endphp
                                                @endif
                                            @endforeach
                                            @if ($item->status === 'active' || $isMember)
                                                <x-user-option :user="$item" :selected="$selected" :pill="true"/>
                                            @endif
                                        @endforeach
                                    </select>
                                    @if ($addEmployeePermission == 'all' || $addEmployeePermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-cleaner" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                        </div>
                    @endif

                    @if ($site->public == 1 && $editProjectMembersPermission || $editPermission == 'all')
                        <div class="col-md-12 d-none" id="add_members">
                            <div class="form-group my-3">
                                <x-forms.label class="my-3" fieldId="selectEmployee" fieldRequired="true"
                                    :fieldLabel="__('modules.sites.addMemberTitle')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control multiple-users" multiple name="user_id[]"
                                        id="selectEmployee" data-live-search="true" data-size="8">
                                        @if ($cleaners != '')

                                            @foreach ($cleaners as $item)
                                                <x-user-option
                                                    :user="$item"
                                                    :pill="true"
                                                    :selected="request()->has('default_assign') && request('default_assign') == $item->id ||(isset($projectTemplateMembers) && in_array($item->id, $projectTemplateMembers))"
                                                />

                                            @endforeach
                                        @endif
                                    </select>

                                    @if ($addEmployeePermission == 'all' || $addEmployeePermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-cleaner" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                        </div>
                    @elseif(in_array('cleaner', user_roles()))
                        <input type="hidden" name="user_id[]" value="{{ user()->id }}">
                    @endif

                    <div class="col-md-12 col-lg-4">
                        <x-forms.select fieldId="project_status"
                            :fieldLabel="__('app.site') . ' ' . __('app.status')" fieldName="status" search="true">
                            @foreach ($projectStatus as $status)
                                <option
                                data-content="<i class='fa fa-circle mr-1 f-15' style='color:{{$status->color}}'></i>{{ $status->status_name }}"
                                @selected($site->status == $status->status_name)
                                value="{{$status->status_name}}">
                                </option>

                            @endforeach


                        </x-forms.select>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <x-forms.range class="mr-0 mr-lg-2 mr-md-2"
                            :disabled="($site->calculate_task_progress != 'manual' ? 'true' : 'false')"
                            :fieldLabel="__('modules.sites.projectCompletionStatus')" fieldName="completion_percent"
                            fieldId="completion_percent" :fieldValue="$site->completion_percent" />
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <x-forms.select fieldId="calculate_task_progress" :fieldLabel="__('modules.sites.calculateProjectProgressThrough')"
                                        fieldName="calculate_task_progress" search="false">
                            <option value="manaul" @if ($site->calculate_task_progress == 'manaul') selected @endif>--</option>
                            <option value="task_completion" @if ($site->calculate_task_progress == 'task_completion') selected @endif>
                                @lang('modules.sites.taskCompletion')
                            </option>
                            <option value="project_total_time" @if ($site->calculate_task_progress == 'project_total_time') selected @endif>
                                @lang('modules.sites.projectTotalTime')
                            </option>
                            <option value="project_deadline" @if ($site->calculate_task_progress == 'project_deadline') selected @endif>
                                @lang('modules.sites.basedOnProjectDeadline')
                            </option>
                        </x-forms.select>
                    </div>


                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-top-grey">
                    @lang('modules.customer.clientOtherDetails')</h4>

                <div class="row p-20">
                    <div class="col-lg-4">
                        <x-forms.select fieldId="currency_id" :fieldLabel="__('modules.invoices.currency')"
                            fieldName="currency_id" search="true">
                            @foreach ($currencies as $currency)
                                <option @selected($currency->id == $site->currency_id) value="{{ $currency->id }}">
                                    {{ $currency->currency_symbol . ' (' . $currency->currency_code . ')' }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.sites.projectBudget')"
                            fieldName="project_budget" fieldId="project_budget" :fieldValue="$site->project_budget"
                            :fieldPlaceholder="__('placeholders.price')" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.sites.hours_allocated')"
                            fieldName="hours_allocated" fieldId="hours_allocated"
                            :fieldValue="$site->hours_allocated" :fieldPlaceholder="__('placeholders.hourEstimate')" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="manual_timelog"
                                    :fieldLabel="__('modules.sites.manualTimelog')" :checked="($site->manual_timelog
                                    == 'enable')" fieldName="manual_timelog" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4" id="clientNotification">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="client_task_notification" :checked="($site->allow_client_notification
                                == 'enable')"
                                    :fieldLabel="__('modules.sites.clientTaskNotification')"
                                    fieldName="client_task_notification" />
                            </div>
                        </div>
                    </div>

                    @if ($editPermission == 'all')
                        <div class="col-lg-3 col-md-6">
                            @php
                                $activeEmployees = $cleaners->filter(function ($cleaner) {
                                    return $cleaner->status !== 'deactive';
                                });

                                $selectedEmployee = $cleaners->firstWhere('id', $site->added_by);

                                if ($selectedEmployee && $selectedEmployee->status === 'deactive') {
                                    $employeesToShow = $activeEmployees->push($selectedEmployee);
                                } else {
                                    $employeesToShow = $activeEmployees;
                                }
                            @endphp
                            <x-forms.select fieldId="added_by" :fieldLabel="__('app.added').' '.__('app.by')"
                                fieldName="added_by">
                                <option value="">--</option>
                                @foreach ($employeesToShow as $item)
                                    <x-user-option :user="$item" :selected="$site->added_by == $item->id" />
                                @endforeach
                            </x-forms.select>
                        </div>
                    @endif



                </div>

                <div class="row p-20">
                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="miroboard_checkbox"
                                    :fieldLabel="__('modules.sites.enableMiroboard')" fieldName="miroboard_checkbox"
                                    :checked="$site ? $site->enable_miroboard : ''"/>
                            </div>
                        </div>
                    </div>
                    <input type = "hidden" name = "mention_user_ids" id = "mentionUserId" class ="mention_user_ids">

                    <div class="col-md-6 col-lg-6 {{!is_null($site) && $site->enable_miroboard ? '' : 'd-none'}}" id="miroboard_detail">
                        <div class="form-group my-3">
                            <div class="row">
                                <div class="col-md-6 mt-6">
                                    <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.sites.miroBoardId')"
                                        fieldName="miro_board_id" fieldRequired="true" fieldId="miro_board_id" :fieldValue="$site->miro_board_id"/>
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <div class="d-flex mt-5">
                                    <x-forms.checkbox fieldId="client_access"
                                        :fieldLabel="__('modules.sites.clientMiroAccess')" fieldName="client_access"
                                        :checked="$site ? $site->client_access : ''"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <x-forms.custom-field :fields="$fields" :model="$site"></x-forms.custom-field>


                <x-form-actions>
                    <x-forms.button-primary id="save-site-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('sites.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script>
    $(document).ready(function() {

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        $(".multiple-users").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        const dp1 = datepicker('#start_date', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $site->start_date) }}"),
            onSelect: (instance, date) => {
                dp2.setMin(date);
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#deadline', {
            position: 'bl',
            dateSelected: new Date("{{ $site->deadline ? str_replace('-', '/', $site->deadline) : str_replace('-', '/', now(company()->timezone)) }}"),
            onSelect: (instance, date) => {
                dp1.setMax(date);
            },
            ...datepickerConfig
        });

        @if ($site->deadline == null)
            $('#deadlineBox').hide();
        @endif

        $('#without_deadline').click(function() {
            var check = $('#without_deadline').is(":checked") ? true : false;
            if (check == true) {
                $('#deadlineBox').hide();
                $('#without_deadline').closest('.col-md-6.col-lg-4').removeClass('col-md-6 col-lg-4').addClass('col-md-8');
            } else {
                $('#deadlineBox').show();
                $('#without_deadline').closest('.col-md-8').removeClass('col-md-8').addClass('col-md-6 col-lg-4');
            }
        });
        const atValues = @json($userData);

        quillMention(atValues, '#project_summary');

        $('#createProjectLabel').click(function () {
            let projectId = "{{ $site->id }}";
            const url = "{{ route('site-label.create') }}?project_id=" + projectId;
            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        $('#save-site-form').click(function() {
            var note = document.getElementById('project_summary').children[0].innerHTML;
            document.getElementById('project_summary-text').value = note;

            var user = $('#project_summary span[data-id]').map(function(){
                            return $(this).attr('data-id')
                        }).get();

            var mention_user_id  =  $.makeArray(user);
            $('#mentionUserId').val(mention_user_id.join(','));
            const url = "{{ route('sites.update', $site->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-site-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file:true,
                buttonSelector: "#save-site-form",
                data: $('#save-site-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });

        $('#addProjectCategory').click(function() {
            const url = "{{ route('projectCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#zone-setting').click(function() {
            const url = "{{ route('zones.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#client_view_task').change(function() {
            $('#clientNotification').toggleClass('d-none');
        });

        $('#is_private').change(function() {
            $('#add_members').toggleClass('d-none');
            $('#edit_members').addClass('d-none');
        });

        $('#is_public').change(function() {
            $('#edit_members').toggleClass('d-none');
            $('#add_members').addClass('d-none');
        });

        $('#miroboard_checkbox').change(function() {
            $('#miroboard_detail').toggleClass('d-none');
        });

        $('#add-customer').click(function() {
            $(MODAL_XL).modal('show');

            const url = "{{ route('customers.create') }}";

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

        $('#calculate-service job-progress').change(function() {
            if ($(this).is(':checked')) {
                $('#completion_percent').attr('disabled', 'true');
            } else {
                $('#completion_percent').removeAttr('disabled');
            }
        });

        // Handle progress calculation method changes
        $('#calculate_task_progress').change(function () {
            var selectedValue = $(this).val();
            var withoutDeadlineCheckbox = $('#without_deadline');
            var deadlineBox = $('#deadlineBox');
            
            if (selectedValue === 'project_deadline') {
                // If deadline-based calculation is selected, disable "without deadline" checkbox
                withoutDeadlineCheckbox.prop('disabled', true);
                deadlineBox.show();
                withoutDeadlineCheckbox.closest('.col-md-6.col-lg-4').removeClass('col-md-8').addClass('col-md-6 col-lg-4');
            } else {
                // Enable "without deadline" checkbox for other methods
                withoutDeadlineCheckbox.prop('disabled', false);
            }
        });

        // Handle "without deadline" checkbox changes
        $('#without_deadline').change(function () {
            var isChecked = $(this).is(':checked');
            var calculateProgressSelect = $('#calculate_task_progress');
            var selectedValue = calculateProgressSelect.val();
            
            if (isChecked && selectedValue === 'project_deadline') {
                // If "without deadline" is checked and deadline-based calculation is selected,
                // change to manual calculation
                calculateProgressSelect.val('').trigger('change');
            }
        });

        <x-forms.custom-field-filejs/>

        init(RIGHT_MODAL);
    });

    $('#save-site-data-form').on('change', '#employee_department', function () {
            let id = $(this).val();
            if (id === '' || id.length === 0) {
                id = 0;
            }
            let userId = '{{ $site->members->pluck("user.id")->implode(",") }}';
            let url = "{{ route('zones.members', ':id') }}?userId="+userId;
            url = url.replace(':id', id);

            $.easyAjax({
                url: url,
                type: "GET",
                container: '#save-site-data-form',
                blockUI: true,
                redirect: true,
                success: function (data) {
                    $('#selectEmployee').html(data.data);
                    $('#selectEmployee').selectpicker('refresh');
                }
            })
        });

</script>
