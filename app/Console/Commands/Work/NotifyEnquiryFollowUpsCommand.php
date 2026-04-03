<?php

declare(strict_types=1);

namespace App\Console\Commands\Work;

use App\Models\Company;
use App\Models\Crm\Enquiry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyEnquiryFollowUpsCommand extends Command
{
    protected $signature = 'enquiries:notify-followups
                            {--company= : Only run for a specific company ID}';

    protected $description = 'Notify staff of enquiries whose follow-up date has arrived';

    public function handle(): int
    {
        $companyIds = $this->option('company')
            ? [(int) $this->option('company')]
            : Company::query()->pluck('id')->all();

        $totalNotified = 0;

        foreach ($companyIds as $companyId) {
            try {
                $enquiries = Enquiry::query()
                    ->dueFollowUps($companyId)
                    ->with('customer')
                    ->get();

                foreach ($enquiries as $enquiry) {
                    Log::info('Enquiry follow-up due', [
                        'company_id'  => $companyId,
                        'enquiry_id'  => $enquiry->id,
                        'enquiry_name' => $enquiry->name,
                        'follow_up_at' => $enquiry->follow_up_at?->toDateTimeString(),
                        'note'        => $enquiry->follow_up_note,
                    ]);

                    $totalNotified++;
                }

                if ($enquiries->isNotEmpty()) {
                    $this->line("Company {$companyId}: {$enquiries->count()} follow-up(s) due.");
                }
            } catch (\Throwable $th) {
                Log::error('NotifyEnquiryFollowUps: company ' . $companyId . ': ' . $th->getMessage());
                $this->error("Company {$companyId} failed: " . $th->getMessage());
            }
        }

        $this->info("Done. Follow-ups notified: {$totalNotified}");

        return self::SUCCESS;
    }
}
