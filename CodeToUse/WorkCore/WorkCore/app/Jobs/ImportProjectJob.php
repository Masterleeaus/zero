<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Site;
use App\Models\ProjectMember;
use App\Scopes\ActiveScope;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\UniversalSearchTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Service Agreements\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ProjectActivity;
use App\Traits\ExcelImportable;

class ImportProjectJob implements ShouldQueue
{

    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UniversalSearchTrait;
    use ExcelImportable;

    private $row;
    private $columns;
    private $company;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->isColumnExists('project_name') && $this->isColumnExists('start_date')) {
            $customer = null;

            if (!empty($this->isColumnExists('client_email'))) {
                // user that have customer role
                $customer = User::where('email', $this->getColumnValue('client_email'))->whereHas('roles', function ($q) {
                    $q->where('name', 'customer');
                })->first();
            }

            DB::beginTransaction();
            try {
                $site = new Site();
                $site->company_id = $this->company?->id;
                $site->project_name = $this->getColumnValue('project_name');

                $site->project_summary = $this->isColumnExists('project_summary') ? $this->getColumnValue('project_summary') : null;

                $site->start_date = Carbon::createFromFormat('Y-m-d', $this->getColumnValue('start_date'));
                $site->deadline = $this->isColumnExists('deadline') ? (!empty(trim($this->getColumnValue('deadline'))) ? Carbon::createFromFormat('Y-m-d', $this->getColumnValue('deadline')) : null) : null;

                if ($this->isColumnExists('notes')) {
                    $site->notes = $this->getColumnValue('notes');
                }

                $site->client_id = $customer ? $customer->id : null;

                $site->project_budget = $this->isColumnExists('project_budget') ? $this->getColumnValue('project_budget') : null;

                $site->currency_id = $this->company?->currency_id;

                $site->status = $this->isColumnExists('status') ? strtolower(trim($this->getColumnValue('status'))) : 'not started';

                $site->save();

                // Process site members if column exists
                if ($this->isColumnExists('project_members')) {
                    $membersEmails = $this->getColumnValue('project_members');
                    if (!empty($membersEmails)) {
                        $this->syncProjectMembers($site, $membersEmails);
                    }
                }

                $this->logSearchEntry($site->id, $site->project_name, 'sites.show', 'site', $site->company_id);
                $this->logProjectActivity($site->id, 'team chat.updateSuccess');
                DB::commit();
            } catch (InvalidFormatException $e) {
                DB::rollBack();
                $this->failJob(__('team chat.invalidDate'));
            } catch (Exception $e) {
                DB::rollBack();
                $this->failJobWithMessage($e->getMessage());
            }

        }
        else {
            $this->failJob(__('team chat.invalidData'));
        }
    }

    public function logProjectActivity($projectId, $text)
    {
        $activity = new ProjectActivity();
        $activity->project_id = $projectId;
        $activity->activity = $text;
        $activity->save();
    }

    /**
     * Sync site members from comma-separated emails
     *
     * @param Site $site
     * @param string $emailsString
     * @return void
     */
    private function syncProjectMembers(Site $site, string $emailsString)
    {
        // Parse comma-separated emails
        $emails = array_map('trim', explode(',', $emailsString));
        $emails = array_filter($emails, function($email) {
            return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if (empty($emails)) {
            return;
        }

        $userIds = [];

        foreach ($emails as $email) {
            // Find user by email, check if exists and is active
            $user = User::withoutGlobalScope(ActiveScope::class)
                ->where('email', $email)
                ->where('company_id', $this->company?->id)
                ->first();

            // Only add if user exists and is active
            if ($user && $user->status === 'active') {
                $userIds[] = $user->id;
            }
        }

        // Sync users as site members (without detaching existing members)
        if (!empty($userIds)) {
            $site->projectMembers()->syncWithoutDetaching($userIds);
        }
    }

}
