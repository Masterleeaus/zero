@php
    $editUnitPermission = user()->permission('edit_tenan');
    $deleteUnitPermission = user()->permission('delete_tenan');
@endphp
<!-- ROW START -->
<div class="row mt-4">
    <div class="col-xl-7 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <x-cards.data>
            <div class="row mb-3">
                <div class="col-10">
                    <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-0">
                        @lang('trpackage::app.pickup.showPickup') @lang('app.details')
                    </h4>
                </div>
                <div class="col-2 text-right">
                    <div class="dropdown">
                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                            <a class="dropdown-item openRightModal"
                                href="{{ route('pickup.edit', $card->id) }}">@lang('app.edit')</a>
                            <a class="dropdown-item delete-unit">@lang('app.delete')</a>
                        </div>
                    </div>
                </div>
            </div>
            <x-cards.data-row :label="__('trpackage::app.menu.tanggalDiterima')" :value="\Carbon\Carbon::createFromFormat('Y-m-d', $card->paket->tanggal_diterima)->format('Y-m-d')" />
            <x-cards.data-row :label="__('trpackage::app.menu.namaEkspedisi')" :value="mb_ucwords($card->paket->ekspedisi_id  ? $card->paket->ekspedisi->name : '--')" />
            <x-cards.data-row :label="__('trpackage::app.menu.namaPengirim')" :value="mb_ucwords($card->paket->nama_pengirim ? $card->paket->nama_pengirim : '--')" />
            <x-cards.data-row :label="__('trpackage::app.menu.hpPengirim')" :value="mb_ucwords($card->paket->no_hp_pengirim ? $card->paket->no_hp_pengirim : '--')" />
            <x-cards.data-row :label="__('trpackage::app.menu.unit')" :value="$card->unit_id  ? $card->unit->unit_name : '--'" />
            <x-cards.data-row :label="__('trpackage::app.menu.namaPenerima')" :value="mb_ucwords($card->nama_penerima ? $card->nama_penerima : '--')" />
            <x-cards.data-row :label="__('trpackage::app.menu.jamDiterima')" :value="mb_ucwords($card->paket->jam ? $card->paket->jam : '--')" />
            <x-cards.data-row :label="__('trpackage::app.menu.status')" :value="mb_ucwords($card->status_ambil ? $card->status_ambil : '--')" />
            <x-cards.data-row :label="__('trpackage::app.menu.note')" :value="mb_ucwords($card->paket->catatan_penerima ? $card->paket->catatan_penerima : '--')" />
            <x-cards.data-row :label="__('trpackage::app.menu.namaPengambil')" :value="$card->nama_pengambil ? mb_ucwords($card->nama_pengambil) : '--'" />
            <x-cards.data-row :label="__('trpackage::app.menu.hpPengambil')" :value="$card->no_hp_pengambil ? mb_ucwords($card->no_hp_pengambil) : '--'" />
            <x-cards.data-row :label="__('trpackage::app.menu.kartuIdentitas')" :value="$card->id_card_pengambil ? mb_ucwords($card->id_card_pengambil) : '--'" />
            <x-cards.data-row :label="__('trpackage::app.menu.tanggalDiambil')" :value="$card->tanggal_pengambil ? \Carbon\Carbon::createFromFormat('Y-m-d', $card->tanggal_pengambil)->format('Y-m-d') : '--'" />
            <x-cards.data-row :label="__('trpackage::app.menu.jamDiterima')" :value="$card->jam_ambil ? mb_ucwords($card->jam_ambil) : '--'" />
            <x-cards.data-row :label="__('trpackage::app.menu.note')" :value="$card->catatan_pengambil ? mb_ucwords($card->catatan_pengambil) : '--'" />
        </x-cards.data>
    </div>
    <div class="col-xl-5 col-lg-12 col-md-12 ">
        <div class="row">
            <div class="col-md-12">
                <x-cards.data>
                    <div class="row">
                        <div class="col-6">
                            <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-2">
                                @lang('trpackage::app.menu.fotoPenerima')
                            </h4>
                            <img src="{{ $url ? $url : '--' }}" heigt="100%">
                        </div>
                        <div class="col-6">
                            <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-2">
                                @lang('trpackage::app.menu.fotoPengambil')
                            </h4>
                            @if ($card->foto_pengambil != '')
                                <img src="{{ $url_ambil }}" height="100%">
                            @else
                            <span>@lang('trpackage::app.menu.noImage')</span>
                            @endif
                        </div>
                    </div>
                </x-cards.data>
            </div>
        </div>
    </div>
</div>
<!-- ROW END -->

<script>
    $('body').on('click', '.delete-unit', function() {
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('pickup.destroy', $card->id) }}";

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            window.location.href = response.redirectUrl;
                        }
                    }
                });
            }
        });
    });
</script>
