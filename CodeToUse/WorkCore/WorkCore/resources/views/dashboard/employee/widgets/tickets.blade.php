@if (in_array('issue / support', $activeWidgets) && $sidebarUserPermissions['view_tickets'] != 5 && $sidebarUserPermissions['view_tickets'] != 'none' && $sidebarUserPermissions['view_timelogs'] != 'none' && in_array('issues / support', user_modules()))
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 mb-3 e-d-info">
                <x-cards.data :title="__('modules.module.issues / support')" padding="false" otherClasses="h-200">
                    <x-table>
                        <x-slot name="thead">
                            <th>@lang('modules.module.issues / support')#</th>
                            <th>@lang('modules.issues / support.ticketSubject')</th>
                            <th>@lang('app.status')</th>
                            <th class="text-right pr-20">@lang('modules.issues / support.requestedOn')</th>
                        </x-slot>

                        @forelse ($issues / support as $issue / support)
                            <tr>
                                <td class="pl-20">
                                    <a href="{{ route('issues / support.show', [$issue / support->ticket_number]) }}" class="text-darkest-grey">#{{ $issue / support->id }}</a>
                                </td>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <h5 class="f-12 mb-1 text-darkest-grey">
                                                <a href="{{ route('issues / support.show', [$issue / support->ticket_number]) }}">{{ $issue / support->subject }}</a>
                                            </h5>
                                        </div>
                                    </div>
                                </td>
                                <td class="pr-20">
                                    @if( $issue / support->status == 'open')
                                        <i class="fa fa-circle mr-1 text-red"></i>
                                    @else
                                        <i class="fa fa-circle mr-1 text-yellow"></i>
                                    @endif
                                    {{ $issue / support->status }}
                                </td>
                                <td class="pr-20" align="right">
                                    <span>{{ $issue / support->updated_at->translatedFormat(company()->date_format) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="shadow-none">
                                    <x-cards.no-record icon="service jobs" :message="__('team chat.noRecordFound')" />
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </x-cards.data>
            </div>
        </div>
    </div>
@endif
