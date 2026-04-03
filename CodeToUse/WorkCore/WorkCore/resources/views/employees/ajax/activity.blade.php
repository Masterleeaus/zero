<!-- ROW START -->
<div class="row py-0 py-md-0 py-lg-3 mt-4">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- ACTIVITY DETAIL START -->
        <div class="p-activity-detail cal-info b-shadow-4" data-menu-vertical="1" data-menu-scroll="1"
            data-menu-dropdown-timeout="500" id="projectActivityDetail">
            {{-- @dd($histories); --}}
            @forelse($histories as $history)
                <div class="card border-0 b-shadow-4 p-20 rounded">
                    <div class="card-horizontal">
                        <div class="card-img my-1 ml-0">
                            <img src="{{ $history->user->image_url }}" alt="{{ $history->user->name }}">
                        </div>
                        <div class="card-body border-0 pl-0 py-1 mb-2">
                            <div class="d-flex flex-grow-1">
                                <h4 class="card-title f-12 font-weight-normal text-dark mr-3 mb-1">
                                    @if($history->employee_activity == "leave-created" )
                                        {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by')  <span
                                            class="text-darkest-grey">{{ $history->user->name }}</span>
                                            <a
                                            href="{{route('leaves.show', $history->leave_id).'?tab=leaves'}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "leave-updated" )
                                        {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by')  <span
                                            class="text-darkest-grey">{{ $history->user->name }}</span><a
                                            href="{{route('leaves.show', $history->leave_id).'?tab=leaves'}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "leave-deleted" )
                                        {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by')  <span
                                            class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif

                                    @if($history->employee_activity == "service job-created" )
                                        {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                            class="text-darkest-grey">{{ $history->user->name }}</span><a
                                            href="{{route('service jobs.show', $history->task_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "service job-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('service jobs.show', $history->task_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "service job-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "proposal-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('proposals.show', $history->proposal_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "proposal-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('proposals.show', $history->proposal_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "proposal-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "site-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('sites.show', $history->proj_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif

                                    @if($history->employee_activity == "site-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('sites.show', $history->proj_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "site-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "invoice-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('invoices.show', $history->invoice_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "invoice-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('invoices.show', $history->invoice_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "invoice-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "issue / support-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('issues / support.show', $history->ticket_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "issue / support-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('issues / support.show', $history->ticket_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "issue / support-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "quote-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('quotes.show', $history->estimate_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "quote-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('quotes.show', $history->estimate_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "quote-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "deal-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('deals.show', $history->deal_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "deal-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('deals.show', $history->deal_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "deal-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "customer-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('customers.show', $history->client_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "customer-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('customers.show', $history->client_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "customer-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "expenses-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('expenses.show', $history->expenses_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "expenses-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('expenses.show', $history->expenses_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "expenses-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "timelog-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('timelogs.show', $history->timelog_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "timelog-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('timelogs.show', $history->timelog_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "timelog-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "event-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('events.show', $history->event_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "event-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('events.show', $history->event_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "event-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "service / extra-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('services / extras.show', $history->product_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "service / extra-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('services / extras.show', $history->product_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "service / extra-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "creditNote-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('creditnotes.show', $history->credit_note_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "creditNote-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('creditnotes.show', $history->credit_note_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "creditNote-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "payment-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('payments.show', $history->payment_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "payment-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('payments.show', $history->payment_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "payment-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "order-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('orders.show', $history->order_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "order-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('orders.show', $history->order_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "order-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "service agreement-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('service agreements.show', $history->contract_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "service agreement-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('service agreements.show', $history->contract_id)}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "service agreement-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif
                                    @if($history->employee_activity == "followUp-created" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('deals.show', $history->deal_followup_id).'?tab=follow-up'}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "followUp-updated" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span><a
                                        href="{{route('deals.show', $history->deal_followup_id).'?tab=follow-up'}}"> {{__('modules.customer.viewDetails')}}</a>
                                    @endif
                                    @if($history->employee_activity == "followUp-deleted" )
                                    {{ __('modules.cleaners.activities.'.$history->employee_activity) }} @lang('app.by') <span
                                        class="text-darkest-grey">{{ $history->user->name }}</span>
                                    @endif


                                </h4>

                            </div>
                            <div class="card-text f-11 text-lightest text-justify">

                                <span class="f-11 text-lightest">
                                    {{ $history->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format .' '. company()->time_format)  }}</span>
                            </div>
                        </div>
                    </div>
                </div><!-- card end -->
            @empty
                <div class="card border-0 p-20 rounded">
                    <div class="card-horizontal">

                        <div class="card-body border-0 p-0 ml-3">
                            <h4 class="card-title f-14 font-weight-normal">
                                @lang('team chat.noActivityByThisUser')
                            </h4>
                            <p class="card-text f-12 text-dark-grey"></p>
                        </div>
                    </div>
                </div><!-- card end -->
            @endforelse


        </div>
        <!-- ACTIVITY DETAIL END -->
    </div>
</div>
