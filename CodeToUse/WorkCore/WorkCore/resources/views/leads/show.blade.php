@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
$viewClientNote = user()->permission('view_deal_note');
$viewProposalPermission = user()->permission('view_lead_proposals');
$viewLeadFilePermission = user()->permission('view_lead_files');
$viewLeadFollowupPermission = user()->permission('view_lead_follow_up');

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
            <x-tab :href="route('deals.show', $deal->id)" :text="__('modules.sites.overview')" class="profile" />
            @if($viewLeadFilePermission != 'none')
                <x-tab :href="route('deals.show', $deal->id).'?tab=files'" :text="__('modules.enquiry.file')" class="files" ajax="false"/>
            @endif
            @if($viewLeadFollowupPermission != 'none')
                <x-tab :href="route('deals.show', $deal->id).'?tab=follow-up'" :text="__('modules.enquiry.followUp')" class="follow-up" ajax="false" />
            @endif
            @if($viewProposalPermission != 'none')
                <x-tab :href="route('deals.show', $deal->id).'?tab=proposals'" :text="__('modules.enquiry.proposal')" class="proposals" ajax="false" />
            @endif
            @if ($viewClientNote != 'none')
                <x-tab :href="route('deals.show', $deal->id).'?tab=notes'" ajax="false" :text="__('app.notes')" class="notes" />
            @endif

            @if ($gdpr->enable_gdpr)
                <x-tab :href="route('deals.show', $deal->id).'?tab=gdpr'" :text="__('app.menu.gdpr')" class="gdpr" ajax="false" />
            @endif

            <x-tab :href="route('deals.show', $deal->id).'?tab=history'" :text="__('modules.service jobs.history')" class="history" ajax="false" />
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

        $('body').on('click', '#add-files', function() {
            const url = "{{ route('deal-files.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

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
                    var url = "{{ route('deals.destroy', ':id') }}";
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
                                window.location.href = "{{ route('deals.index')}}";
                            }
                        }
                    });
                }
            });
        });

    </script>
@endpush
