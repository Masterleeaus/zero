<?php

use App\Helper\NumberFormat;
use App\Models\Company;
use App\Models\Service Agreement;
use App\Models\CreditNotes;
use App\Models\Quote;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        if (!Schema::hasColumn('credit_notes', 'original_credit_note_number')) {
            Schema::table('credit_notes', function (Blueprint $table) {
                $table->string('original_credit_note_number')->nullable()->after('cn_number');
            });
        }

        if (!Schema::hasColumn('service agreements', 'original_contract_number')) {
            Schema::table('service agreements', function (Blueprint $table) {
                $table->string('contract_number')->nullable()->change();
                $table->string('original_contract_number')->nullable()->after('contract_number');
            });
        }

        if (!Schema::hasColumn('quotes', 'original_estimate_number')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->string('estimate_number')->nullable()->change();
                $table->string('original_estimate_number')->nullable()->after('estimate_number');
            });
        }

        if (!Schema::hasColumn('orders', 'original_order_number')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('order_number')->nullable()->change();
                $table->string('original_order_number')->nullable()->after('order_number');
            });
        }

        if (!Schema::hasColumn('invoices', 'original_invoice_number')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('invoice_number')->change();
                $table->string('original_invoice_number')->nullable()->after('invoice_number');
            });
        }


        $companeis = Company::get();

        foreach ($companeis as $company) {
            $invoiceSettings = InvoiceSetting::withoutGlobalScopes()->where('company_id', $company->id)->first();

            $creditNotes = CreditNotes::withoutGlobalScopes()->where('company_id', $company->id)->get();

            foreach ($creditNotes as $creditNote) {
                $creditNote->cn_number = NumberFormat::creditNote($creditNote->cn_number, $invoiceSettings);
                $creditNote->original_credit_note_number = str($creditNote->cn_number)->replace($invoiceSettings->credit_note_prefix . $invoiceSettings->credit_note_number_separator, '');
                $creditNote->saveQuietly();
            }

            $service agreements = Service Agreement::withoutGlobalScopes()->where('company_id', $company->id)->get();

            foreach ($service agreements as $service agreement) {
                $service agreement->contract_number = NumberFormat::service agreement($service agreement->contract_number, $invoiceSettings);
                $service agreement->original_contract_number = str($service agreement->contract_number)->replace($invoiceSettings->contract_prefix . $invoiceSettings->contract_number_separator, '');
                $service agreement->saveQuietly();
            }

            $invoices = Invoice::withoutGlobalScopes()->where('company_id', $company->id)->get();

            foreach ($invoices as $invoice) {
                $invoice->invoice_number = NumberFormat::invoice($invoice->invoice_number, $invoiceSettings);
                $invoice->original_invoice_number = str($invoice->invoice_number)->replace($invoiceSettings->invoice_prefix . $invoiceSettings->invoice_number_separator, '');
                $invoice->saveQuietly();
            }

            $quotes = Quote::withoutGlobalScopes()->where('company_id', $company->id)->get();

            foreach ($quotes as $quote) {
                $quote->estimate_number = NumberFormat::quote($quote->estimate_number, $invoiceSettings);
                $quote->original_estimate_number = str($quote->estimate_number)->replace($invoiceSettings->estimate_prefix . $invoiceSettings->estimate_number_separator, '');
                $quote->saveQuietly();
            }

            $orders = Order::withoutGlobalScopes()->where('company_id', $company->id)->get();

            foreach ($orders as $order) {
                $order->order_number = NumberFormat::order($order->order_number, $invoiceSettings);
                $order->original_order_number = str($order->order_number)->replace($invoiceSettings->order_prefix . $invoiceSettings->order_number_separator, '');
                $order->saveQuietly();
            }
        }

    }

};
