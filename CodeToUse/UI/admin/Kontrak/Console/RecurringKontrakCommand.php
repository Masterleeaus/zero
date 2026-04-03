<?php

namespace Modules\Kontrak\Console;

use App\Models\Company;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Traits\UniversalSearchTrait;
use Modules\Kontrak\Entities\RecurringKontrak;

class RecurringKontrakCommand extends Command
{

    use UniversalSearchTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring-kontrak-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto create recurring kontrak';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $companies = Company::select('id')->get();
        foreach ($companies as $company) {
            $recurringKontrak = RecurringKontrak::with(['recurrings'])->where('company_id', $company->id)->where('status', 'active')->get();
            foreach ($recurringKontrak as $recurring) {
                if (is_null($recurring->next_schedule_date)) {
                    continue;
                }

                $totalExistingCount = $recurring->recurrings->count();

                if ($recurring->unlimited_recurring == 1 || ($totalExistingCount < $recurring->billing_cycle)) {

                    if ($recurring->next_schedule_date->timezone($recurring->company->timezone)->isToday()) {
                        $this->kontrakCreate($recurring);
                        $this->saveNextScheduleDate($recurring);
                    }
                }
            }

        }
    }

    private function saveNextScheduleDate($recurring)
    {
        $days = match ($recurring->rotation) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'bi-weekly' => now()->addWeeks(2),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addQuarter(),
            'half-yearly' => now()->addMonths(6),
            'annually' => now()->addYear(),
            default => now()->addDay(),
        };

        $recurring->next_schedule_date = $days->format('Y-m-d');
        $recurring->save();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function kontrakCreate($recurringsData)
    {
        $recurring = $recurringsData;
        $invoice = new Invoice();
        $invoice->project_id = $recurring->project_id;
        $invoice->client_id = $recurring->client_id;
        $invoice->issue_date = Carbon::now()->format('Y-m-d');
        $invoice->sub_total = $recurring->rate;
        $invoice->total = $recurring->rate;
        $invoice->due_amount = $recurring->amount;
        $invoice->currency_id = $recurring->currency_id;
        $invoice->default_currency_id = company()->currency_id;
        $invoice->recurring = 'no';
        $invoice->invoice_number = Invoice::lastInvoiceNumber() + 1;
        $invoice->company_address_id = company()->id;
        $invoice->save();
        // $invoice->unit_id = $contract_detail->unit_id;
        // $invoice->due_date = Carbon::createFromFormat($this->company->date_format, $request->due_date)->format('Y-m-d');
        // $invoice->exchange_rate = $request->exchange_rate;
        // $invoice->billing_frequency = $request->recurring_payment == 'yes' ? $request->billing_frequency : null;
        // $invoice->billing_interval = $request->recurring_payment == 'yes' ? $request->billing_interval : null;
        // $invoice->billing_cycle = $request->recurring_payment == 'yes' ? $request->billing_cycle : null;
        // $invoice->note = trim_editor($request->note);
        // $invoice->show_shipping_address = $request->show_shipping_address;
        // $invoice->estimate_id = $request->estimate_id ? $request->estimate_id : null;
        // $invoice->bank_account_id = $request->bank_account_id;
        // Log search
        $this->logSearchEntry($invoice->id, $invoice->issue_date, 'kontrak.show', 'kontrak');
    }

}
