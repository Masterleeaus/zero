@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">

                            <a class="nav-item nav-link f-15 active typehouse" href="{{ route('house-settings.index') }}"
                                role="tab" aria-controls="nav-house-settings"
                                aria-selected="true">@lang('houses::app.menu.typehouse')
                            </a>

                            <a class="nav-item nav-link f-15 tower" href="{{ route('house-settings.index') }}?tab=tower"
                                role="tab" aria-controls="nav-towers"
                                aria-selected="true">@lang('houses::app.menu.tower')
                            </a>

                            <a class="nav-item nav-link f-15 area"
                                href="{{ route('house-settings.index') }}?tab=area" role="tab"
                                aria-controls="nav-areas" aria-selected="true">@lang('houses::app.menu.area')
                            </a>
                        </div>
                    </nav>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="row">

                    <div class="col-md-12 mb-2">
                        <x-forms.button-primary icon="plus" id="addTypeHouse" class="typehouse-btn mb-2 d-none actionBtn">
                            @lang('app.addNew')
                            @lang('houses::modules.house.typehouse')
                        </x-forms.button-primary>

                        <x-forms.button-primary icon="plus" id="addTower" class="tower-btn mb-2 d-none actionBtn">
                            @lang('app.addNew')
                            @lang('houses::modules.house.tower')
                        </x-forms.button-primary>

                        <x-forms.button-primary icon="plus" id="addArea" class="area-btn mb-2 d-none actionBtn">
                            @lang('app.addNew')
                            @lang('houses::app.menu.area')
                        </x-forms.button-primary>
                    </div>

                </div>
            </x-slot>

            {{-- include tabs here --}}
            @include($view)

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('scripts')
    <script>
        /* manage menu active class */
        $('.nav-item').removeClass('active');
        const activeTab = "{{ $activeTab }}";
        $('.' + activeTab).addClass('active');

        showBtn(activeTab);

        function showBtn(activeTab) {
            $('.actionBtn').addClass('d-none');
            $('.' + activeTab + '-btn').removeClass('d-none');
        }

       $("body").on("click", "#editSettings .nav a", function(event) {
            event.preventDefault();

            $('.nav-item').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: "#nav-tabContent",
                historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        showBtn(response.activeTab);

                        $('#nav-tabContent').html(response.html);
                        init('#nav-tabContent');
                    }
                }
            });
        });

        $('body').on('click', '.delete-typehouse', function() {
            var id = $(this).data('typehouse-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.removeTicketText')",
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
                    var url = "{{ route('typehouses.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                $('.row' + id).fadeOut(100);
                            }
                        }
                    });
                }
            });
        });



        $('body').on('click', '.delete-tower', function() {
            var id = $(this).data('tower-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.removeChannelText')",
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
                    var url = "{{ route('towers.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                $('.row' + id).fadeOut(100);
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.edit-tower', function() {
            var typeId = $(this).data('tower-id');
            var url = "{{ route('towers.edit', ':id') }}";
            url = url.replace(':id', typeId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open edit reply area modal */
        $('body').on('click', '.edit-area', function() {
            var areaId = $(this).data('area-id');
            var url = "{{ route('areas.edit', ':id') }}";
            url = url.replace(':id', areaId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open edit reply area modal */
        $('body').on('click', '.edit-typehouse', function() {
            var typehouseId = $(this).data('typehouse-id');
            var url = "{{ route('typehouses.edit', ':id') }}";
            url = url.replace(':id', typehouseId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });



        /* delete area */
        $('body').on('click', '.delete-area', function() {
            var id = $(this).data('area-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.removeTemplateText')",
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
                    var url = "{{ route('areas.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                $('.row' + id).fadeOut(100);
                            }
                        }
                    });
                }
            });
        });

        /* open add agent modal */
        $('body').on('click', '#addTypeHouse', function() {
            var url = "{{ route('typehouses.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open add agent modal */
        $('body').on('click', '#addTower', function() {
            var url = "{{ route('towers.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        /* open creat reply area modal */
        $('body').on('click', '#addArea', function() {
            var url = "{{ route('areas.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });



    </script>
@endpush
