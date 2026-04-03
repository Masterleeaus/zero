<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('trworkpermits::app.workpermits.edit')</h4>
                <div class="row p-20">
                    <div class="col-md-2">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('trworkpermits::app.menu.date')"
                            fieldRequired="true"></x-forms.label>
                        <div class="input-group">
                            <input type="text" name="date" class="px-6 form-control height-35 rounded p-0 f-15"
                                value="{{ $wp->date }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.text fieldId="company_name" :fieldLabel="__('trworkpermits::app.menu.companyName')" fieldName="company_name"
                            fieldRequired="true" :fieldValue="$wp->company_name">
                        </x-forms.text>
                    </div>

                    <div class="col-md-7">
                        <x-forms.text fieldId="company_address" :fieldLabel="__('trworkpermits::app.menu.companyAddress')" fieldName="company_address"
                            fieldRequired="true" :fieldValue="$wp->company_address">
                        </x-forms.text>
                    </div>

                    <div class="col-md-2">
                        <x-forms.label class=" mt-3" fieldId="phone" :fieldLabel="__('trworkpermits::app.menu.noHP')"
                            fieldRequired="true"></x-forms.label>
                        <div class="input-group">
                            <input type="number" id="phone" name="phone"
                                class="px-6 form-control height-35 rounded p-0 f-15" value="{{ $wp->phone }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.text fieldId="project_manj" :fieldLabel="__('trworkpermits::app.menu.projectManj')" fieldName="project_manj"
                            fieldRequired="true" :fieldValue="$wp->project_manj">
                        </x-forms.text>
                    </div>

                    <div class="col-md-3">
                        <x-forms.text fieldId="site_coor" :fieldLabel="__('trworkpermits::app.menu.siteCoor')" fieldName="site_coor" fieldRequired="true"
                            :fieldValue="$wp->site_coor">
                        </x-forms.text>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('trworkpermits::app.menu.jenisPekerjaan')"
                            fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="jenis_pekerjaan" id="jenis_pekerjaan"
                            data-live-search="true">
                            <option @if ('renovasi' == $wp->jenis_pekerjaan) selected @endif value="renovasi">Renovasi</option>
                            <option @if ('non-renovasi' == $wp->jenis_pekerjaan) selected @endif value="non-renovasi">Non Renovasi
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('trworkpermits::app.menu.lingkupPekerjaan')"
                            fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="lingkup_pekerjaan" id="lingkup_pekerjaan"
                            data-live-search="true">
                            <option @if ('interior' == $wp->lingkup_pekerjaan) selected @endif value="interior">Interior</option>
                            <option @if ('mechanical' == $wp->lingkup_pekerjaan) selected @endif value="mechanical">Mechanical
                            </option>
                            <option @if ('electrical' == $wp->lingkup_pekerjaan) selected @endif value="electrical">Electrical
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('trworkpermits::app.menu.unit')"
                            fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="unit_id" id="unit_id"
                            data-live-search="true">
                            @foreach ($units as $items)
                                <option @if ($items->id == $wp->unit_id) selected @endif value="{{ $items->id }}">
                                    {{ $items->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="date_start" :fieldLabel="__('trworkpermits::app.menu.dateStart')"
                            fieldRequired="true"></x-forms.label>
                        <div class="input-group">
                            <input type="text" id="date_start" name="date_start"
                                class="px-6 form-control height-35 rounded p-0 f-15"
                                value="{{ \Carbon\Carbon::createFromFormat('Y-m-d', $wp->date_start)->format('d-m-Y') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="date_end" :fieldLabel="__('trworkpermits::app.menu.dateEnd')"
                            fieldRequired="true"></x-forms.label>
                        <div class="input-group">
                            <input type="text" id="date_end" name="date_end"
                                class="px-6 form-control height-35 rounded p-0 f-15"
                                value="{{ \Carbon\Carbon::createFromFormat('Y-m-d', $wp->date_end)->format('d-m-Y') }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <x-forms.label class="my-3" fieldId="description-text" :fieldLabel="__('trworkpermits::app.menu.detailPekerjaan')"
                                fieldRequired="true">
                            </x-forms.label>
                            <textarea name="detail_pekerjaan" id="description-text" rows="4" class="form-control">{{ $wp->detail_pekerjaan }}</textarea>
                        </div>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-leave-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('work-permits.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>


<script>
    $(document).ready(function() {
        const dp2 = datepicker('#date_start', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $wp->date_start) }}"),
            ...datepickerConfig
        });

        const dp3 = datepicker('#date_end', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $wp->date_end) }}"),
            ...datepickerConfig
        });

        $('#save-leave-form').click(function() {
            const url = "{{ route('work-permits.update', $wp->id) }}";
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
