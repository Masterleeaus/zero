<?php

namespace App\Providers;

use App\Models\Money\Invoice;
use App\Models\Money\Payment;
use App\Models\Money\Quote;
use App\Models\UserSupport;
use App\Policies\InvoicePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\QuotePolicy;
use App\Policies\UserSupportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Quote::class        => QuotePolicy::class,
        Invoice::class      => InvoicePolicy::class,
        Payment::class      => PaymentPolicy::class,
        UserSupport::class  => UserSupportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Passport::enablePasswordGrant();
        $this->registerPolicies();
    }
}
