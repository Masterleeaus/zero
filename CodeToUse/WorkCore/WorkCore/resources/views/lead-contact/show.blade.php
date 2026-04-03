@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
$viewClientNote = user()->permission('view_lead_note');
$viewDeals = user()->permission('view_deals');
@endphp


@section('filter-section')
    <!-- FILTER START -->
    <!-- PROJECT HEADER START -->
    <div class="d-flex filter-box site-header bg-white">

        <div class="mobile-close-overlay w-100 h-100" id="close-customer-overlay"></div>
        <div class="site-menu d-lg-flex" id="mob-customer-detail">
            <a class="d-none close-it" href="javascript:;" id="close-customer-detail" >
                <i class="fa fa-times"></i>
            </a>
            <x-tab :href="route('enquiry-contact.show', $leadContact->id)" :text="__('modules.enquiry.profile')" class="profile" />

                @if ($viewDeals == 'all'
                || ($viewDeals == 'added' && $leadContact->added_by == user()->id)
                || ($viewDeals == 'owned' && $leadContact->lead_owner == user()->id)
                || ($viewDeals == 'both' && ($leadContact->added_by == user()->id || $leadContact->lead_owner == user()->id))
                )
                <x-tab :href="route('enquiry-contact.show', $leadContact->id).'?tab=deal'" :text="__('modules.leadContact.deal')" class="deal" ajax="false"/>
            @endif

            @if ($viewClientNote == 'all' || $viewClientNote == 'both' || $viewClientNote == 'added' || $viewClientNote == 'owned')
                <x-tab :href="route('enquiry-contact.show', $leadContact->id).'?tab=notes'" ajax="false" :text="__('app.notes')" class="notes" />
            @endif
        </div>
        <a class="mb-0 d-block d-lg-none text-dark-grey ml-auto mr-2 border-left-grey"
            onclick="openClientDetailSidebar()"><i class="fa fa-ellipsis-v "></i></a>
    </div>
    <!-- FILTER END -->
    <!-- PROJECT HEADER END -->

@endsection

@section('content')

    <div class="content-wrapper border-top-0 customer-detail-wrapper">
        @include($view)
    </div>

@endsection

@push('scripts')
    <script>
        $("body").on("click", ".ajax-tab", function(event) {
            event.preventDefault();

            $('.site-menu .p-sub-menu').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: ".content-wrapper",
                historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        $('.content-wrapper').html(response.html);
                        init('.content-wrapper');
                    }
                }
            });
        });

    </script>
    <script>
        const activeTab = "{{ $activeTab }}";
        $('.site-menu .' + activeTab).addClass('active');

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: "@lang('team chat.sweetAlertTitle')",
                text: "@lang('team chat.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('team chat.confirmDelete')",
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
                    var url = "{{ route('enquiry-contact.destroy', ':id') }}";
                    url = url.replace(':id', id);

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
                                window.location.href = "{{ route('enquiry-contact.index')}}";
                            }
                        }
                    });
                }
            });
        });

    </script>
@endpush
