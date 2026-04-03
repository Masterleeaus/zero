<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('trpackage::app.pickup.editPickup')
                </h4>
                <div class="row p-20">
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-md-2">
                                <x-forms.text fieldId="" :fieldLabel="__('trpackage::app.menu.date')" fieldName=""
                                    fieldRequired="" :fieldValue="\Carbon\Carbon::createFromFormat('Y-m-d', $card->paket->tanggal_diterima)->format('d-m-Y')" :fieldReadOnly="true">
                                </x-forms.text>
                            </div>
                            <div class="col-md-3">
                                <x-forms.text fieldId="" :fieldLabel="__('trpackage::app.menu.namaEkspedisi')" fieldName=""
                                    fieldRequired="" :fieldValue="$card->paket->ekspedisi->name" :fieldReadOnly="true">
                                </x-forms.text>
                            </div>
                            <div class="col-md-4">
                                <x-forms.text fieldId="" :fieldLabel="__('trpackage::app.menu.namaPengirim')" fieldName=""
                                    fieldRequired="" :fieldValue="$card->paket->nama_pengirim" :fieldReadOnly="true">
                                </x-forms.text>
                            </div>
                            <div class="col-md-3">
                                <x-forms.text fieldId="" :fieldLabel="__('trpackage::app.menu.hpPengirim')" fieldName=""
                                    fieldRequired="" :fieldValue="$card->paket->no_hp_pengirim" :fieldReadOnly="true">
                                </x-forms.text>
                            </div>
                            <div class="col-md-2">
                                <x-forms.text fieldId="" :fieldLabel="__('trpackage::app.menu.jamDiterima')" fieldName=""
                                fieldRequired="" :fieldValue="$card->paket->jam" :fieldReadOnly="true">
                                </x-forms.text>
                            </div>
                            <div class="col-md-10">
                                <x-forms.text fieldId="name" :fieldLabel="__('trpackage::app.menu.note')" fieldName="catatan_penerima"
                                    fieldRequired="" :fieldValue="$card->paket->catatan_penerima" :fieldReadOnly="true">
                                </x-forms.text>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-3">
                        <img src="{{ $url }}" class="border rounded">
                    </div>
                </div>
                <hr>
                <div class="row m-4 border">
                    <div class="col-lg-4">
                        <x-forms.text fieldId="" :fieldLabel="__('trpackage::app.menu.unit')" fieldName=""
                        fieldRequired="" :fieldValue="$card->unit->unit_name" :fieldReadOnly="true">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-4">
                        <x-forms.text fieldId="" :fieldLabel="__('app.type')" fieldName=""
                        fieldRequired="" :fieldValue="$card->type->name" :fieldReadOnly="true">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-4">
                        <x-forms.text fieldId="" :fieldLabel="__('trpackage::app.menu.namaPenerima')" fieldName=""
                        fieldRequired="" :fieldValue="$card->nama_penerima" :fieldReadOnly="true">
                        </x-forms.text>
                    </div>
                </div>
                <div class="row p-20">
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-md-2">
                                <x-forms.label class="mt-3" fieldId="date" :fieldLabel="__('trpackage::app.menu.tanggalDiambil')" fieldRequired="true">
                                </x-forms.label>
                                <div class="input-group">
                                    <input type="text" id="date2" name="tanggal_pengambil"
                                        class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15"
                                        placeholder="@lang('placeholders.date')"
                                        value="{{ empty($card->tanggal_pengambil) ? \Carbon\Carbon::today()->format('d-m-Y') : \Carbon\Carbon::createFromFormat('Y-m-d', $card->tanggal_pengambil)->format('d-m-Y') }}">

                                </div>
                            </div>
                            <div class="col-md-4">
                                <x-forms.text fieldId="name" :fieldLabel="__('trpackage::app.menu.namaPengambil')" fieldName="nama_pengambil"
                                    fieldRequired="true" :fieldValue="$card->nama_pengambil">
                                </x-forms.text>
                            </div>
                            <div class="col-md-3">
                                <x-forms.label class=" mt-3" fieldId="no_hp" :fieldLabel="__('trpackage::app.menu.hpPengambil')"
                                    fieldRequired="true"></x-forms.label>
                                <div class="input-group">
                                    <input type="number" id="no_hp" name="no_hp_pengambil"
                                        class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15" value="{{ $card->no_hp_pengambil }}">
                                </div>
                            </div>
                            {{-- <div class="col-md-3">
                                <x-forms.label class=" mt-3" fieldId="no_hp" :fieldLabel="__('trpackage::app.menu.kartuIdentitas')"
                                    fieldRequired="true"></x-forms.label>
                                <div class="input-group">
                                    <input type="number" id="id_card_pengambil" name="id_card_pengambil"
                                        class="px-6 position-relative text-dark font-weight-normal form-control height-35 rounded p-0 text-left f-15" value="{{ $card->id_card_pengambil }}">
                                </div>
                            </div> --}}
                            <div class="col-md-3">
                                <x-forms.text fieldId="name" :fieldLabel="__('trpackage::app.menu.kartuIdentitas')" fieldName="id_card_pengambil"
                                    fieldRequired="true" :fieldValue="$card->id_card_pengambil">
                                </x-forms.text>
                            </div>
                            <div class="col-md-2">
                                <div class="bootstrap-timepicker timepicker">
                                    <x-forms.text :fieldLabel="__('trpackage::app.menu.jamAmbil')" :fieldPlaceholder="__('placeholders.hours')" fieldName="jam_ambil"
                                        fieldId="akhir" fieldRequired="true" :fieldValue="$card->jam_ambil"/>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <x-forms.label class="mt-3" fieldId="parent_label" :fieldLabel="__('trpackage::app.menu.status')"
                                    fieldName="parent_label" fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="status_ambil" id="cond_rating">
                                        <option @if ("new" == $card->status_ambil) selected @endif value="new">New</option>
                                        <option @if ("finished" == $card->status_ambil) selected @endif value="finished">Finished</option>
                                    </select>
                                </x-forms.input-group>
                            </div>
                            <div class="col-md-7">
                                <x-forms.text fieldId="name" :fieldLabel="__('trpackage::app.menu.note')" fieldName="catatan_pengambil"
                                    fieldRequired="true" :fieldValue="$card->catatan_pengambil">
                                </x-forms.text>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <x-forms.file allowedFileExtensions="png jpg jpeg svg" class="mr-0 mr-lg-2 mr-md-2 cropper"
                            :fieldLabel="__('trpackage::app.menu.fotoPengambil')" fieldName="foto_pengambil" fieldId="image" fieldHeight="119" :fieldValue="$url_ambil" />
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-leave-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('pickup.index')" class="border-0">@lang('app.cancel')
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

        $('#awal, #akhir').timepicker({
            @if (company()->time_format == 'H:i')
                showMeridian: false,
            @endif
        }).on('hide.timepicker', function(e) {
            calculateTime();
        });

        const dp1 = datepicker('#date2', {
            position: 'bl',
            ...datepickerConfig
        });

        $('#save-leave-form').click(function() {
            const url = "{{ route('pickup.update', $card->id) }}";
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
