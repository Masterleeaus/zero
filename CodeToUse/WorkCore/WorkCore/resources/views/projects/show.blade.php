@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
$viewProjectMemberPermission = user()->permission('view_project_members');
$viewProjectMilestonePermission = ($site->project_admin == user()->id) ? 'all' : user()->permission('view_project_milestones');
$viewTasksPermission = ($site->project_admin == user()->id) ? 'all' : user()->permission('view_project_tasks');
$viewGanttPermission = ($site->project_admin == user()->id) ? 'all' : user()->permission('view_project_gantt_chart');
$viewInvoicePermission = user()->permission('view_project_invoices');
$viewEstimatePermission = user()->permission('view_project_estimates');
$viewDiscussionPermission = user()->permission('view_project_discussions');
$viewNotePermission = user()->permission('view_project_note');
$viewFilesPermission = user()->permission('view_project_files');
$viewRatingPermission = user()->permission('view_project_rating');
$viewOrderPermission = user()->permission('view_project_orders');

$projectArchived = $site->trashed();
@endphp


@section('filter-section')
    <!-- FILTER START -->
    <!-- PROJECT HEADER START -->

    <div class="d-flex d-lg-block filter-box site-header bg-white">
        <div class="mobile-close-overlay w-100 h-100" id="close-customer-overlay"></div>

        <div class="site-menu" id="mob-customer-detail">
            <a class="d-none close-it" href="javascript:;" id="close-customer-detail">
                <i class="fa fa-times"></i>
            </a>

            <nav class="tabs">
                <ul class="-primary">
                    <li>
                        <x-tab :href="route('sites.show', $site->id)" :text="__('modules.sites.overview')" class="overview" />
                    </li>

                    @if (
                        !$site->public && $viewProjectMemberPermission == 'all'
                    )
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=members'" :text="__('modules.sites.members')"
                            class="members" />
                        </li>
                    @endif

                    @if ($viewFilesPermission == 'all' || ($viewFilesPermission == 'added' && user()->id == $site->added_by) || ($viewFilesPermission == 'owned' && user()->id == $site->client_id))
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=files'" :text="__('modules.sites.files')"
                            class="files" />
                        </li>
                    @endif

                    @if ($viewProjectMilestonePermission == 'all' || $viewProjectMilestonePermission == 'added' || ($viewProjectMilestonePermission == 'owned' && user()->id == $site->client_id))
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=milestones'"
                            :text="__('modules.sites.milestones')" class="milestones" />
                        </li>
                    @endif

                    @if (in_array('service jobs', user_modules()) && ($viewTasksPermission == 'all' || ($viewTasksPermission == 'added' && user()->id == $site->added_by) || ($viewTasksPermission == 'owned' && user()->id == $site->client_id)))
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=service jobs'" :text="__('app.menu.service jobs')" class="service jobs"
                            ajax="false" />
                        </li>

                        @if (!$projectArchived)
                            <li>
                                <x-tab :href="route('sites.show', $site->id).'?tab=taskboard'" :text="__('modules.service jobs.taskBoard')" class="taskboard" ajax="false" />
                            </li>

                            @if ($viewGanttPermission == 'all' || ($viewGanttPermission == 'added' && user()->id == $site->added_by) || ($viewGanttPermission == 'owned' && user()->id == $site->client_id))
                                <li>
                                    <x-tab :href="route('sites.show', $site->id).'?tab=gantt'" ajax="false" :text="__('modules.sites.viewGanttChart')" class="gantt" />
                                </li>
                            @endif
                        @endif
                    @endif

                    @if (in_array('invoices', user_modules()) && !is_null($site->client_id) && ($viewInvoicePermission == 'all' || ($viewInvoicePermission == 'added' && user()->id == $site->added_by) || ($viewInvoicePermission == 'owned' && user()->id == $site->client_id)))
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=invoices'" :text="__('app.menu.invoices')" class="invoices" ajax="false" />
                        </li>
                    @endif

                    @if (in_array('quotes', user_modules()) && ($viewEstimatePermission == 'all' || ($viewEstimatePermission == 'added' && user()->id == $site->added_by) || ($viewEstimatePermission == 'owned' && user()->id == $site->client_id)))
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=quotes'" :text="__('app.menu.quotes')" class="quotes" ajax="false" />
                        </li>
                    @endif

                    @if (in_array('orders', user_modules()) && !is_null($site->client_id) && ($viewOrderPermission == 'all' || ($viewOrderPermission == 'added' && user()->id == $site->added_by) || ($viewOrderPermission == 'owned' && user()->id == $site->client_id)))
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=orders'" :text="__('app.menu.orders')" class="orders" ajax="false" />
                        </li>
                    @endif

                    @if (in_array('timelogs', user_modules()) && ($viewProjectTimelogPermission == 'all' || ($viewProjectTimelogPermission == 'added' && user()->id == $site->added_by) || ($viewProjectTimelogPermission == 'owned' && user()->id == $site->client_id)))
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=timelogs'" :text="__('app.menu.timeLogs')" class="timelogs" ajax="false" />
                        </li>
                    @endif

                    @if (in_array('expenses', user_modules()) && ($viewExpensePermission == 'all' || ($viewExpensePermission == 'added' && user()->id == $site->added_by) || ($viewExpensePermission == 'owned' && user()->id == $site->client_id)))
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=expenses'" :text="__('app.menu.expenses')" class="expenses" ajax="false" />
                        </li>
                    @endif

                    @if ($viewMiroboardPermission == 'all' && $site->enable_miroboard &&
                    ((in_array('customer', user_roles()) && $site->client_access && $site->client_id == user()->id)
                    || !in_array('customer', user_roles()))
                    )
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=miroboard'" :text="__('app.menu.miroboard')" class="miroboard" ajax="false" />
                        </li>
                    @endif

                    @if (in_array('payments', user_modules()) && !is_null($site->client_id) && ($viewPaymentPermission == 'all' || ($viewPaymentPermission == 'added' && user()->id == $site->added_by) || ($viewPaymentPermission == 'owned' && user()->id == $site->client_id)))
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=payments'" :text="__('app.menu.payments')" class="payments" ajax="false" />
                        </li>
                    @endif

                    @if ($viewDiscussionPermission == 'all' || ($viewDiscussionPermission == 'added' && user()->id == $site->added_by) || ($viewDiscussionPermission == 'owned' && user()->id == $site->client_id))
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=discussion'" :text="__('modules.sites.discussion')" class="discussion" ajax="false" />
                        </li>
                    @endif

                    @if ($viewNotePermission != 'none' )
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=notes'" :text="__('modules.sites.note')" class="notes" ajax="false" />
                        </li>
                    @endif

                    @if ($viewRatingPermission != 'none' && !is_null($site->client_id))
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=rating'" :text="__('modules.sites.rating')" class="rating" ajax="false" />
                        </li>
                    @endif

                    @if($viewBurndownChartPermission != 'none' || $site->project_admin == user()->id)
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=burndown-chart'"
                                :text="__('modules.sites.burndownChart')" class="burndown-chart" ajax="false" />
                        </li>
                    @endif

                    @if (!in_array('customer', user_roles()))
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=activity'"
                                :text="__('modules.cleaners.activity')" class="activity" />
                        </li>
                    @endif

                    @if ($viewNotePermission != 'none' )
                        <li>
                            <x-tab :href="route('sites.show', $site->id).'?tab=issues / support'" :text="__('app.menu.issues / support')" class="issues / support" ajax="false" />
                        </li>
                    @endif
                </ul>
            </nav>
        </div>

        <a class="mb-0 d-block d-lg-none text-dark-grey ml-auto mr-2 border-left-grey" onclick="openClientDetailSidebar()"><i class="fa fa-ellipsis-v "></i></a>
    </div>



    <!-- PROJECT HEADER END -->

@endsection

@section('content')

    <div class="content-wrapper pt-0 border-top-0 customer-detail-wrapper">
        @include($view)
    </div>

@endsection

@push('scripts')

    <script>
        $("body").on("click", ".site-menu .ajax-tab", function(event) {
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

    </script>
    <script>
        /*******************************************************
                 More btn in sites menu Start
        *******************************************************/

        const container = document.querySelector('.tabs');
        const primary = container.querySelector('.-primary');
        const primaryItems = container.querySelectorAll('.-primary > li:not(.-more)');
        container.classList.add('--jsfied'); // insert "more" button and duplicate the list

        primary.insertAdjacentHTML('beforeend', `
        <li class="-more">
            <button type="button" class="px-4 h-100 bg-grey d-none d-lg-flex align-items-center" aria-haspopup="true" aria-expanded="false">
            {{__('app.more')}} <span>&darr;</span>
            </button>
            <ul class="-secondary" id="hide-site-menues">
            ${primary.innerHTML}
            </ul>
        </li>
        `);
        const secondary = container.querySelector('.-secondary');
        const secondaryItems = secondary.querySelectorAll('li');
        const allItems = container.querySelectorAll('li');
        const moreLi = primary.querySelector('.-more');
        const moreBtn = moreLi.querySelector('button');
        moreBtn.addEventListener('click', e => {
            e.preventDefault();
            container.classList.toggle('--show-secondary');
            moreBtn.setAttribute('aria-expanded', container.classList.contains('--show-secondary'));
        }); // adapt tabs

        const doAdapt = () => {
            // reveal all items for the calculation
            allItems.forEach(item => {
                item.classList.remove('--hidden');
            }); // hide items that won't fit in the Primary

            let stopWidth = moreBtn.offsetWidth;
            let hiddenItems = [];
            const primaryWidth = primary.offsetWidth;
            primaryItems.forEach((item, i) => {
                if (primaryWidth >= stopWidth + item.offsetWidth) {
                    stopWidth += item.offsetWidth;
                } else {
                    item.classList.add('--hidden');
                    hiddenItems.push(i);
                }
            }); // toggle the visibility of More button and items in Secondary

            if (!hiddenItems.length) {
                moreLi.classList.add('--hidden');
                container.classList.remove('--show-secondary');
                moreBtn.setAttribute('aria-expanded', false);
            } else {
                secondaryItems.forEach((item, i) => {
                    if (!hiddenItems.includes(i)) {
                        item.classList.add('--hidden');
                    }
                });
            }
        };

        doAdapt(); // adapt immediately on load

        window.addEventListener('resize', doAdapt); // adapt on window resize
        // hide Secondary on the outside click

        document.addEventListener('click', e => {
            let el = e.target;

            while (el) {
                if (el === secondary || el === moreBtn) {
                    return;
                }

                el = el.parentNode;
            }

            container.classList.remove('--show-secondary');
            moreBtn.setAttribute('aria-expanded', false);
        });
        /*******************************************************
                 More btn in sites menu End
        *******************************************************/
    </script>
@endpush
