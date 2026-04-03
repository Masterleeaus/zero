<!-- ROW START -->
<div class="row">
    <div class="col-sm-12">
        @if (!$customer->admin_approval)
            <x-alert type="danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa fa-user-times"></i> @lang('modules.dashboard.verificationPending')
                    </div>
                    <div>
                        <x-forms.button-primary class="verify-user" icon="check">
                            @lang('app.approve')
                        </x-forms.button-primary>
                    </div>
                </div>
            </x-alert>
        @endif
    </div>

    <!--  USER CARDS START -->
    <div class="col-xl-7 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">
        <div class="row">

            <div
                @class([
                    'col-lg-6 col-md-6 mb-4 mb-lg-0',
                    'col-xl-12' => !in_array('sites', user_modules()),
                    'col-xl-7' => in_array('sites', user_modules())
                ])>

                <x-cards.user :image="$customer->image_url">
                    <div class="row">
                        <div class="col-10">
                            <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-0">
                                {{ $customer->name_salutation }}
                                @isset($customer->country)
                                    <x-flag :country="$customer->country" />
                                @endisset
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
                                        href="{{ route('customers.edit', $customer->id) }}">@lang('app.edit')</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="f-13 font-weight-normal text-dark-grey mb-0">
                        {{ $customer->clientDetails->company_name }}
                    </p>
                    <p class="card-text f-12 text-lightest mb-1">@lang('app.lastLogin')

                        @if (!is_null($customer->last_login))
                            {{ $customer->last_login->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                        @else
                            --
                        @endif
                    </p>

                    @if ($customer->status != 'active')
                        <p class="card-text f-12 text-dark-grey">
                            <x-status :value="__('app.inactive')" color="red" />
                        </p>
                    @endif


                </x-cards.user>

            </div>
            @if(in_array('sites', user_modules()))
                <div class="col-xl-5 col-lg-6 col-md-6">
                    <x-cards.widget :title="__('modules.dashboard.totalProjects')" :value="$clientStats->totalProjects"
                        icon="layer-group" />
                </div>
            @endif
        </div>
    </div>
    <!--  USER CARDS END -->


        <!--  WIDGETS START -->
        <div class="col-xl-5 col-lg-12 col-md-12">
            <div class="row">
                @if(in_array('payments', user_modules()))
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-4 mb-lg-0 mb-md-0">
                        <x-cards.widget :title="__('modules.dashboard.totalEarnings')"
                            :value="$earningTotal" icon="coins" />
                    </div>
                @endif
                @if(in_array('invoices', user_modules()))
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <x-cards.widget :title="__('modules.dashboard.totalUnpaidInvoices')"
                            :value="$clientStats->totalUnpaidInvoices" icon="file-invoice-dollar" />
                    </div>
                @endif
            </div>
        </div>
        <!--  WIDGETS END -->

</div>
<!-- ROW END -->

<!-- ROW START -->
<div class="row mt-4">
    <div class="col-xl-7 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <x-cards.data :title="__('modules.customer.profileInfo')">
            <x-cards.data-row :label="__('modules.cleaners.fullName')" :value="$customer->name_salutation" />

            <x-cards.data-row :label="__('app.email')" :value="$customer->email ?? '--'" />

            <x-cards.data-row :label="__('modules.customer.companyName')"
                :value="$customer->clientDetails->company_name ?? '--'" />

            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                    @lang('modules.profile.companyLogo')</p>
                <p class="mb-0 text-dark-grey f-14 w-70">
                    @if ($customer->clientDetails->company_logo)
                        <img data-toggle="tooltip" style="height:50px;"
                    src="{{ $customer->clientDetails->image_url }}">
                    @else
                    --
                    @endif
                </p>
            </div>

            <x-cards.data-row :label="__('app.mobile')"
                :value="$customer->mobile_with_phonecode" />

            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                    @lang('modules.cleaners.gender')</p>
                <p class="mb-0 text-dark-grey f-14 w-70">
                    <x-gender :gender='$customer->gender' />
                </p>
            </div>

            <x-cards.data-row :label="__('modules.customer.officePhoneNumber')"
                :value="$customer->clientDetails->office ?? '--'" />

            <x-cards.data-row :label="__('modules.customer.website')" :value="$customer->clientDetails->website ?? '--'" />

            <x-cards.data-row :label="__('app.gstNumber')" :value="$customer->clientDetails->gst_number ?? '--'" />

            <x-cards.data-row :label="__('app.address')" :value="$customer->clientDetails->address ?? '--'" />

            <x-cards.data-row :label="__('modules.stripeCustomerAddress.state')"
                :value="$customer->clientDetails->state ?? '--'" />

            <x-cards.data-row :label="__('modules.stripeCustomerAddress.city')"
                :value="$customer->clientDetails->city ?? '--'" />

            <x-cards.data-row :label="__('modules.stripeCustomerAddress.postalCode')"
                :value="$customer->clientDetails->postal_code ?? '--'" />

            <x-cards.data-row :label="__('app.language')"
                :value="$clientLanguage->label. ' '. $clientLanguage->language_name ?? '--'" />

            @if(!is_null($customer->clientDetails->added_by))
                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block ">
                        @lang('app.addedBy')</p>
                    <p class="mb-0 text-dark-grey f-14 ">
                        <x-cleaner :user="$customer->clientDetails->addedBy" />
                    </p>
                </div>
            @endif

            {{-- <x-cards.data-row :label="__('app.note')" html="true" :value="$customer->clientDetails->note ?? '--'" /> --}}

            {{-- Custom fields data --}}
            <x-forms.custom-field-show :fields="$fields" :model="$clientDetail"></x-forms.custom-field-show>

        </x-cards.data>
    </div>
    <div class="col-xl-5 col-lg-12 col-md-12 ">
        <div class="row">
            <div class="col-md-12">
                <x-cards.data :title="__('app.menu.sites')">
                    @if (array_sum($projectChart['values']) > 0)
                        <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen" data-chart-id="site-chart" data-chart-data="{{ json_encode($projectChart) }}"><i class="fas fa-expand float-right mr-3"></i></a>
                    @endif
                    <x-pie-chart id="site-chart" :labels="$projectChart['labels']" :values="$projectChart['values']"
                        :colors="$projectChart['colors']" height="220" width="220" />
                </x-cards.data>
            </div>
        </div>
        @if(in_array('invoices', user_modules()))
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card bg-white border-0 b-shadow-4">
                    <x-cards.data :title="__('app.menu.invoices')">
                        @if (array_sum($invoiceChart['values']) > 0)
                            <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen" data-chart-id="invoice-chart" data-chart-data="{{ json_encode($invoiceChart) }}"><i class="fas fa-expand float-right mr-3"></i></a>
                        @endif
                        <x-pie-chart id="invoice-chart" :labels="$invoiceChart['labels']"
                            :values="$invoiceChart['values']" :colors="$invoiceChart['colors']" height="230"
                            width="220" />
                    </x-cards.data>
                </div>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- ROW END -->

<script>
    $('body').on('click', '.verify-user', function() {
        const id = $(this).data('user-id');
        Swal.fire({
            title: "@lang('team chat.sweetAlertTitle')",
            text: "@lang('team chat.approvalWarning')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('app.approve')",
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
                var url = "{{ route('customers.approve', $customer->id) }}";

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });
</script>
