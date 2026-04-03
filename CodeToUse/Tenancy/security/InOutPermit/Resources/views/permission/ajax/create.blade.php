<div class="row">
    <div class="col-sm-12">
        <x-form id="save-unit-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('trinoutpermit::app.trinoutpermit.addTrInOutPermit')</h4>
                <div class="row p-20">
                    <div class="col-md-12">
                        <div class="border p-2">
                            <x-forms.label fieldId="parent_label" :fieldLabel="__('trinoutpermit::app.menu.notes')" fieldName="parent_label">
                            </x-forms.label>
                            <p class="mb-0">
                                @foreach ($notes as $index => $note)
                                    @if ($index > 0)
                                        <br>
                                    @endif
                                    - {{ $note->remark }}
                                @endforeach
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('trinoutpermit::app.menu.submitBy')" fieldName="parent_label">
                        </x-forms.label>
                        @if (in_array('client', user_roles()))
                            <input type="text" name="pembawa_brg" class="form-control height-35 rounded f-15"
                                value="penghuni" readonly>
                        @else
                            <select class="form-control select-picker" name="pembawa_brg" id="pembawa_brg"
                                data-live-search="true">
                                <option value="">--</option>
                                <option value="penghuni">Penghuni</option>
                                <option value="kontraktor">Kontraktor</option>
                                <option value="supplier">Supplier</option>
                            </select>
                        @endif
                    </div>

                    <div class="col-md-2">
                        <x-forms.label class=" mt-3" fieldId="date" :fieldLabel="__('trinoutpermit::app.menu.date')"
                            fieldRequired="true"></x-forms.label>
                        <div class="input-group">
                            <input type="text" id="date" name="date"
                                class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                placeholder="@lang('placeholders.date')"
                                value="{{ now(company()->timezone)->translatedFormat('d-m-Y') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bootstrap-timepicker timepicker">
                            <x-forms.text class="a-timepicker" :fieldLabel="__('Time')"
                                :fieldPlaceholder="__('placeholders.hours')" fieldName="jam"
                                fieldId="clock-in-time" fieldRequired="true" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('trinoutpermit::app.menu.unit')" fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="unit_id" id="unit_id"
                            data-live-search="true">
                            <option value="">--</option>
                            @foreach ($units as $items)
                                <option value="{{ $items->id }}">{{ $items->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <x-forms.text fieldId="name" :fieldLabel="__('trinoutpermit::app.menu.resident')" fieldName="name" fieldRequired="true"
                            :fieldPlaceholder="__('')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-3">
                        <x-forms.text fieldId="pj" :fieldLabel="__('trinoutpermit::app.menu.delivererName')" fieldName="pj" fieldRequired="true"
                            :fieldPlaceholder="__('')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-3">
                        <x-forms.text fieldId="no_hp" :fieldLabel="__('trinoutpermit::app.menu.noHP')" fieldName="no_hp" fieldRequired="true"
                            :fieldPlaceholder="__('')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('trinoutpermit::app.menu.kartuIdentitas')"
                            fieldName="parent_label" fieldRequired="true">
                        </x-forms.label>
                        <select class="form-control select-picker" name="identity" id="identity"
                            data-live-search="true">
                            <option value="">--</option>
                            <option value="ktp">KTP</option>
                            <option value="sim">SIM</option>
                            <option value="kitas">KITAS</option>
                            <option value="others">Others</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class=" mt-3" fieldId="identity_number" :fieldLabel="__('trinoutpermit::app.menu.noKartu')" fieldRequired="true">
                        </x-forms.label>
                        <div class="input-group">
                            <input type="string" id="identity_number" name="identity_number"
                                class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                placeholder="">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('app.type')" fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="keterangan" id="keterangan"
                            data-live-search="true">
                            <option value="">--</option>
                            <option value="in">Masuk ke dalam Unit</option>
                            <option value="out">Keluar dari dalam Unit</option>
                            <option value="transfer">Pindah antar Unit</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <x-forms.label class="my-3" fieldId="description-text" :fieldLabel="__('trinoutpermit::app.menu.remark')"
                                fieldRequired="true">
                            </x-forms.label>
                            <textarea name="jenis_barang" id="description-text" rows="4" class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-unit-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('trinoutpermit.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        if ($('.custom-date-picker').length > 0) {
            datepicker('.custom-date-picker', {
                position: 'bl',
                ...datepickerConfig
            });
        }

        const dp1 = datepicker('#date', {
            position: 'bl',
            ...datepickerConfig
        });

        $('#clock-in-time').timepicker({
            @if(company()->time_format == 'H:i')
            showMeridian: false,
            @endif
            minuteStep: 1
        });

        $('#save-unit-form').click(function() {
            const url = "{{ route('trinoutpermit.store') }}";
            $.easyAjax({
                url: url,
                container: '#save-unit-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-unit-form",
                data: $('#save-unit-data-form').serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
