<?php

namespace App\Providers;

use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\Supplier;
use App\Models\Money\Account;
use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\JournalEntry;
use App\Models\Money\Payment;
use App\Models\Money\Quote;
use App\Models\Money\SupplierBill;
use App\Models\Money\SupplierPayment;
use App\Models\UserSupport;
use App\Models\Work\Department;
use App\Models\Work\Leave;
use App\Models\Work\Shift;
use App\Models\Work\StaffProfile;
use App\Policies\AccountPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\JournalEntryPolicy;
use App\Policies\LeavePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\QuotePolicy;
use App\Policies\ShiftPolicy;
use App\Policies\StaffProfilePolicy;
use App\Policies\SupplierBillPolicy;
use App\Policies\SupplierPaymentPolicy;
use App\Policies\SupplierPolicy;
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
        Quote::class           => QuotePolicy::class,
        Invoice::class         => InvoicePolicy::class,
        Payment::class         => PaymentPolicy::class,
        UserSupport::class     => UserSupportPolicy::class,
        Expense::class         => ExpensePolicy::class,
        Account::class         => AccountPolicy::class,
        JournalEntry::class    => JournalEntryPolicy::class,
        Supplier::class        => SupplierPolicy::class,
        PurchaseOrder::class   => PurchaseOrderPolicy::class,
        SupplierBill::class    => SupplierBillPolicy::class,
        SupplierPayment::class => SupplierPaymentPolicy::class,
        // ── HRM Pass 2 ─────────────────────────────────────────────────────
        StaffProfile::class    => StaffProfilePolicy::class,
        Department::class      => DepartmentPolicy::class,
        Shift::class           => ShiftPolicy::class,
        Leave::class           => LeavePolicy::class,
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

