<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('security::app.menu.goodsInOutValidation')</h4>
                <div class="row p-20">
                    <div class="col-md-2">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('security::app.menu.submittedBy')" fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="pembawa_brg" id="pembawa_brg"
                            data-live-search="true" disabled>
                            <option value="">--</option>
                            <option @if ('penghuni' == $security->pembawa_brg) selected @endif value="penghuni">Penghuni</option>
                            <option @if ('kontraktor' == $security->pembawa_brg) selected @endif value="kontraktor">Kontraktor
                            </option>
                            <option @if ('supplier' == $security->pembawa_brg) selected @endif value="supplier">Supplier</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.date')" fieldRequired="true">
                        </x-forms.label>
                        <div class="input-group">
                            <input type="text" id="date" name="date"
                                class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                placeholder="@lang('placeholders.date')"
                                value="{{ \Carbon\Carbon::createFromFormat('Y-m-d', $security->date)->format('d-m-Y') }}"
                                readonly>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('security::app.menu.penanggungJawab')" fieldName="parent_label">
                        </x-forms.label>
                        <input type="text"  class="form-control height-35 rounded p-0 text-left f-15"
                         value="{{ $security->pj }}" readonly>
                    </div>

                    <div class="col-md-2">
                        <x-forms.label class=" mt-3" fieldId="due_date" :fieldLabel="__('security::app.menu.noHP')" fieldRequired="true">
                        </x-forms.label>
                        <div class="input-group">
                            <input type="number" id="no_hp" name="no_hp"
                                class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                value="{{ $security->no_hp }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('security::app.menu.resident')" fieldName="parent_label">
                        </x-forms.label>
                        <input type="text"  class="form-control height-35 rounded p-0 text-left f-15"
                         value="{{ $security->name }}" readonly>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('security::app.menu.unit')" fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="unit_id" id="unit_id" data-live-search="true"
                            disabled>
                            <option value="">--</option>
                            @foreach ($units as $items)
                                <option @if ($items->id == $security->unit_id) selected @endif value="{{ $items->id }}">
                                    {{ $items->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('app.type')"
                            fieldName="parent_label">
                        </x-forms.label>
                        <select class="form-control select-picker" name="keterangan" id="keterangan"
                            data-live-search="true" disabled>
                            <option value="">--</option>
                            <option @if ('in' == $security->keterangan) selected @endif value="masuk-ke-unit">Masuk ke
                                dalam Unit</option>
                            <option @if ('out' == $security->keterangan) selected @endif value="keluar-dari-unit">Keluar
                                dari dalam Unit</option>
                            <option @if ('transfer' == $security->keterangan) selected @endif value="pindah-antar-unit">Pindah
                                antar Unit</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="my-3" fieldId="description-text" :fieldLabel="__('security::app.menu.remark')"
                            fieldRequired="true">
                        </x-forms.label>
                        <div>
                            {{ $security->jenis_barang }}
                        </div>
                    </div>
                </div>
                <hr class="">
                <div class="row px-3">
                    <div class="col-lg-12">
                        <x-forms.label class="my-3" fieldId="description-text" :fieldLabel="__('security::app.menu.remarkBySecurity')"
                            fieldRequired="true">
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
                    <x-forms.button-cancel :link="route('security-transfer.index')" class="border-0">@lang('app.cancel')
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
            dateSelected: new Date("{{ str_replace('-', '/', $security->date) }}"),
            ...datepickerConfig
        });

        $('#save-leave-form').click(function() {
            const url = "{{ route('security-transfer.validated', $security->id) }}";
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
