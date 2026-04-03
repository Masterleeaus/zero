<script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>
<style>
    .card-img {
        width: 120px;
        height: 120px;
    }

    .card-img img {
        width: 120px;
        height: 120px;
        object-fit: cover;
    }
    .appreciation-count {
        top: -6px;
        right: 10px;
    }

</style>
@php

$showFullProfile = false;
$employeeDetail = $cleaner->employeeDetail;
if ($viewPermission == 'all'
    || ($viewPermission == 'added' && $employeeDetail->added_by == user()->id)
    || ($viewPermission == 'owned' && $employeeDetail->user_id == user()->id)
    || ($viewPermission == 'both' && ($employeeDetail->user_id == user()->id || $employeeDetail->added_by == user()->id))
) {
    $showFullProfile = true;
}

@endphp

@php
$editEmployeePermission = user()->permission('edit_employees');
$viewAppreciationPermission = user()->permission('view_appreciation');
@endphp

<div class="d-lg-flex">

    <div class="w-100 py-0 py-lg-3 py-md-0">
        <!-- ROW START -->
        <div class="row">
            <!--  USER CARDS START -->
            <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">
                <div class="row">
                    <div class="col-xl-7 col-md-6 mb-4 mb-lg-0">

                        <x-cards.user :image="$cleaner->image_url">
                            <div class="row">
                                <div class="col-10">
                                    <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-0">
                                        {{ $cleaner->name_salutation }}
                                        @isset($cleaner->country)
                                            <x-flag :country="$cleaner->country" />
                                        @endisset
                                    </h4>
                                </div>
                                @if ($editEmployeePermission == 'all' || ($editEmployeePermission == 'added' && $cleaner->employeeDetail->added_by == user()->id))
                                    <div class="col-2 text-right">
                                        <div class="dropdown">
                                            <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle"
                                                type="button" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                                <i class="fa fa-ellipsis-h"></i>
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                                aria-labelledby="dropdownMenuLink" tabindex="0">
                                                <a class="dropdown-item openRightModal"
                                                    href="{{ route('cleaners.edit', $cleaner->id) }}">@lang('app.edit')</a>
                                                    @if (module_enabled('Onboarding'))
                                                         @include('onboarding::start-onboarding')
                                                    @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            </div>

                            <p class="f-12 font-weight-normal text-dark-grey mb-0">
                                {{ !is_null($cleaner->employeeDetail) && !is_null($cleaner->employeeDetail->role) ? $cleaner->employeeDetail->role->name : '' }}
                                &bull;
                                {{ isset($cleaner->employeeDetail) && !is_null($cleaner->employeeDetail->zone) && !is_null($cleaner->employeeDetail->zone) ? $cleaner->employeeDetail->zone->team_name : '' }}
                                 <span class="card-text f-12 text-dark-grey m-lg-2">| {{__('app.role')}}: {{$cleaner->roles()->withoutGlobalScopes()->latest()->first()->display_name}}</span>
                            </p>


                                <p class="card-text f-11 text-lightest mb-1">@lang('app.lastLogin')

                                    @if (!is_null($cleaner->last_login))
                                        {{ $cleaner->last_login->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                                    @else
                                        --
                                    @endif
                                </p>

                            @if ($cleaner->status != 'active')

                                <p class="card-text f-12 text-dark-grey">
                                    <x-status :value="__('app.inactive')" color="red" />
                                </p>
                            @endif

                            @if ($showFullProfile)
                                <div class="card-footer bg-white border-top-grey pl-0">
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 "
                                                for="usr">@lang('app.openTasks')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $cleaner->open_tasks_count }}</p>
                                        </span>
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 "
                                                for="usr">@lang('app.menu.sites')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $cleaner->member_count }}</p>
                                        </span>
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 "
                                                for="usr">@lang('modules.cleaners.hoursLogged')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $hoursLogged }}</p>
                                        </span>
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 "
                                                for="usr">@lang('app.menu.issues / support')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $cleaner->agents_count }}</p>
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </x-cards.user>

                        @if ($cleaner->employeeDetail->about_me != '')
                            <x-cards.data :title="__('app.about')" class="mt-4">
                                <div>{{ $cleaner->employeeDetail->about_me }}</div>
                            </x-cards.data>
                        @endif


                        <x-cards.data :title="__('modules.customer.profileInfo')" class=" mt-4">
                            <x-cards.data-row :label="__('modules.cleaners.employeeId')"
                                :value="(!is_null($cleaner->employeeDetail) && !is_null($cleaner->employeeDetail->employee_id)) ? ($cleaner->employeeDetail->employee_id) : '--'" />

                            <x-cards.data-row :label="__('modules.cleaners.fullName')"
                                :value="$cleaner->name" />

                            <x-cards.data-row :label="__('app.role')"
                                :value="(!is_null($cleaner->employeeDetail) && !is_null($cleaner->employeeDetail->role)) ? ($cleaner->employeeDetail->role->name) : '--'" />

                            <x-cards.data-row :label="__('app.zone')"
                                :value="(isset($cleaner->employeeDetail) && !is_null($cleaner->employeeDetail->zone) && !is_null($cleaner->employeeDetail->zone)) ? ($cleaner->employeeDetail->zone->team_name) : '--'" />

                            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                                    @lang('modules.cleaners.gender')</p>
                                <p class="mb-0 text-dark-grey f-14 w-70">
                                    <x-gender :gender='$cleaner->gender' />
                                </p>
                            </div>

                            @php
                                $currentyearJoiningDate = \Carbon\Carbon::parse(now(company()->timezone)->year.'-'.$cleaner->employeeDetail->joining_date->translatedFormat('m-d'));
                                if ($currentyearJoiningDate->copy()->endOfDay()->isPast()) {
                                    $currentyearJoiningDate = $currentyearJoiningDate->addYear();
                                }
                                $diffInHoursJoiningDate = now(company()->timezone)->floatDiffInHours($currentyearJoiningDate, false);

                                $currentDay = \Carbon\Carbon::parse(now(company()->timezone)->toDateTimeString())->startOfDay()->setTimezone('UTC');
                                $joiningDay = $cleaner->employeeDetail->joining_date;

                                $totalWorkYears = $joiningDay->copy()->diffInYears($currentDay);
                                $totalWorkMonths = $joiningDay->copy()->diffInMonths($currentDay);
                            @endphp

                        <x-cards.data-row
                            :label="__('modules.cleaners.workAnniversary')"
                            :value="!is_null($cleaner->employeeDetail) && !is_null($cleaner->employeeDetail->joining_date)
                                ? (
                                    ($diffInHoursJoiningDate > -23 && $diffInHoursJoiningDate <= 0 && $totalWorkYears == 0 )
                                    ? __('modules.dashboard.joinedToday')
                                    : (
                                        ($totalWorkYears > 0 && $totalWorkMonths % 12 == 0)
                                        ? __('app.completed') . ' ' . $totalWorkYears . ' ' . __('app.year')
                                        : $currentyearJoiningDate->longRelativeToNowDiffForHumans()
                                    )
                                )
                                : '--'"
                        />

                            <x-cards.data-row :label="__('modules.cleaners.dateOfBirth')"
                                              :value="(!is_null($cleaner->employeeDetail) && !is_null($cleaner->employeeDetail->date_of_birth)) ? $cleaner->employeeDetail->date_of_birth->translatedFormat('d F') : '--'" />

                            @if ($showFullProfile)
                                <x-cards.data-row :label="__('app.email')" :value="$cleaner->email" />

                                <x-cards.data-row :label="__('app.mobile')"
                                :value="$cleaner->mobile_with_phonecode" />

                                <x-cards.data-row :label="__('modules.cleaners.slackUsername')"
                                    :value="(isset($cleaner->employeeDetail) && !is_null($cleaner->employeeDetail->slack_username)) ? '@'.$cleaner->employeeDetail->slack_username : '--'" />

                                <x-cards.data-row :label="__('modules.cleaners.hourlyRate')"
                                    :value="(!is_null($cleaner->employeeDetail)) ? company()->currency->currency_symbol.$cleaner->employeeDetail->hourly_rate : '--'" />

                                <x-cards.data-row :label="__('app.address')"
                                    :value="$cleaner->employeeDetail->address ?? '--'" />

                                <x-cards.data-row :label="__('app.skills')"
                                    :value="$cleaner->skills() ? implode(', ', $cleaner->skills()) : '--'" />

                                <x-cards.data-row :label="__('app.language')"
                                    :value="$employeeLanguage->language_name ?? '--'" />

                                <x-cards.data-row :label="__('modules.cleaners.probationEndDate')"
                                :value="$cleaner->employeeDetail->probation_end_date ? Carbon\Carbon::parse($cleaner->employeeDetail->probation_end_date)->translatedFormat(company()->date_format) : '--'" />

                                <x-cards.data-row :label="__('modules.cleaners.noticePeriodStartDate')"
                                :value="$cleaner->employeeDetail->notice_period_start_date ? Carbon\Carbon::parse($cleaner->employeeDetail->notice_period_start_date)->translatedFormat(company()->date_format) : '--'" />

                                <x-cards.data-row :label="__('modules.cleaners.noticePeriodEndDate')"
                                :value="$cleaner->employeeDetail->notice_period_end_date ? Carbon\Carbon::parse($cleaner->employeeDetail->notice_period_end_date)->translatedFormat(company()->date_format) : '--'" />

                                <x-cards.data-row :label="__('modules.cleaners.maritalStatus')"
                                :value="$cleaner?->employeeDetail?->marital_status ? $cleaner->employeeDetail->marital_status->label() : '--'" />

                                <x-cards.data-row :label="__('app.menu.businessAddresses')" :value="$companyAddress ? $companyAddress->location : '--'" />

                                <x-cards.data-row :label="__('modules.cleaners.marriageAnniversaryDate')"
                                :value="$cleaner->employeeDetail->marriage_anniversary_date ? Carbon\Carbon::parse($cleaner->employeeDetail->marriage_anniversary_date)->translatedFormat('d F') : '--'" />

                                <x-cards.data-row :label="__('modules.cleaners.employmentType')"
                                :value="$cleaner?->employeeDetail?->employment_type ? __('modules.cleaners.' . $cleaner?->employeeDetail?->employment_type) : '--'" />

                                @if($cleaner->employeeDetail->employment_type == 'internship')
                                    <x-cards.data-row :label="__('modules.cleaners.internshipEndDate')"
                                    :value="$cleaner->employeeDetail->internship_end_date ? Carbon\Carbon::parse($cleaner->employeeDetail->internship_end_date)->translatedFormat(company()->date_format) : '--'" />
                                @endif

                                @if($cleaner->employeeDetail->employment_type == 'on_contract')
                                    <x-cards.data-row :label="__('modules.cleaners.contractEndDate')"
                                    :value="$cleaner->employeeDetail->contract_end_date ? Carbon\Carbon::parse($cleaner->employeeDetail->contract_end_date)->translatedFormat(company()->date_format) : '--'" />
                                @endif

                                <x-cards.data-row :label="__('modules.cleaners.joiningDate')"
                                :value="(!is_null($cleaner->employeeDetail) && !is_null($cleaner->employeeDetail->joining_date)) ? $cleaner->employeeDetail->joining_date->translatedFormat(company()->date_format) : '--'" />

                                <x-cards.data-row :label="__('modules.cleaners.lastDate')"
                                :value="(!is_null($cleaner->employeeDetail) && !is_null($cleaner->employeeDetail->last_date)) ? $cleaner->employeeDetail->last_date->translatedFormat(company()->date_format) : '--'" />


                                {{-- Custom fields data --}}
                                <x-forms.custom-field-show :fields="$fields" :model="$cleaner->employeeDetail"></x-forms.custom-field-show>

                            @endif

                        </x-cards.data>


                    </div>

                    <div class="col-xl-5 col-lg-6 col-md-6">
                        @if (module_enabled('Onboarding'))
                            @include('onboarding::cleaner-profile-boarding')
                        @endif

                        @if ($showFullProfile)
                            <x-cards.data class="mb-4" :title="__('modules.appreciations.appreciation')">
                                @forelse ($cleaner->appreciationsGrouped as $item)
                                <div class="float-left position-relative mb-2" style="width: 50px" data-toggle="tooltip" data-original-title="@if(isset($item->award->title)){{  $item->award->title }} @endif">
                                    @if(isset($item->award->awardIcon->icon))
                                        <x-award-icon :award="$item->award" />
                                    @endif
                                    <span class="position-absolute badge badge-secondary rounded-circle border-additional-grey appreciation-count">{{ $item->no_of_awards }}</span>
                                </div>
                                @empty
                                    <x-cards.no-record icon="medal" :message="__('team chat.noRecordFound')" />
                                @endforelse
                            </x-cards.data>
                        @endif

                        <x-cards.data class="mb-4">
                            <div class="d-flex justify-content-between">
                                    {{-- <div class="col-6">
                                        <p class="f-14 text-dark-grey">@lang('modules.cleaners.reportingTo')</p>
                                        @if ($cleaner->employeeDetail->reportingTo)
                                            <x-cleaner :user="$cleaner->employeeDetail->reportingTo" />
                                        @else
                                        --
                                        @endif
                                    </div> --}}

                                @if ($cleaner->reportingTeam)
                                    <div class="col-12">
                                        <p class="f-14 text-dark-grey">@lang('modules.cleaners.reportingTeam')</p>
                                        @if (count($cleaner->reportingTeam) > 0)
                                            @if (count($cleaner->reportingTeam) > 1)
                                                @foreach ($cleaner->reportingTeam as $item)
                                                    <div class="taskEmployeeImg rounded-circle mr-1">
                                                        <a href="{{ route('cleaners.show', $item->user->id) }}">
                                                            <img data-toggle="tooltip" data-original-title="{{ $item->user->name }}"
                                                                src="{{ $item->user->image_url }}">
                                                        </a>
                                                    </div>
                                                @endforeach
                                            @else
                                                @foreach ($cleaner->reportingTeam as $item)
                                                    <x-cleaner :user="$item->user" />
                                                @endforeach
                                            @endif

                                        @else
                                            --
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </x-cards.data>

                        @if ($showFullProfile)
                            <div class="row">
                                @if (in_array('attendance', user_modules()))
                                    <div class="col-xl-6 col-sm-12 mb-4">
                                        <x-cards.widget :title="__('modules.dashboard.lateAttendanceMark')"
                                            :value="$lateAttendance" :info="__('modules.dashboard.thisMonth')"
                                            icon="map-marker-alt" />
                                    </div>
                                @endif
                                @if (in_array('leaves', user_modules()))
                                    <div class="col-xl-6 col-sm-12 mb-4">
                                        <x-cards.widget :title="__('modules.dashboard.leavesTaken')" :value="$leavesTaken"
                                            :info="__('modules.dashboard.thisMonth')" icon="sign-out-alt" />
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                @if (in_array('service jobs', user_modules()))
                                    <div class="col-md-12 mb-4">
                                        <x-cards.data :title="__('app.menu.service jobs')" padding="false" class="pb-3">
                                            @if (array_sum($taskChart['values']) > 0)
                                                <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen" data-chart-id="service job-chart" data-chart-data="{{ json_encode($taskChart) }}"><i class="fas fa-expand float-right mr-3"></i></a>
                                            @endif
                                            <x-pie-chart id="service job-chart" :labels="$taskChart['labels']"
                                                :values="$taskChart['values']" :colors="$taskChart['colors']" height="250"
                                                width="250" />
                                        </x-cards.data>
                                    </div>
                                @endif
                                @if (in_array('issues / support', user_modules()))
                                    <div class="col-md-12 mb-4">
                                        <x-cards.data :title="__('app.menu.issues / support')" padding="false" class="pb-3">
                                            @if (array_sum($ticketChart['values']) > 0)
                                                <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen" data-chart-id="issue / support-chart" data-chart-data="{{ json_encode($ticketChart) }}"><i class="fas fa-expand float-right mr-3"></i></a>
                                            @endif
                                            <x-pie-chart id="issue / support-chart" :labels="$ticketChart['labels']"
                                                :values="$ticketChart['values']" :colors="$ticketChart['colors']"
                                                height="250" width="250" />
                                        </x-cards.data>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            <!--  USER CARDS END -->

        </div>
        <!-- ROW END -->
    </div>
</div>
