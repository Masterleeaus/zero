<?php

namespace App\Jobs;

use App\Models\EmployeeDetails;
use App\Models\Role;
use App\Models\User;
use App\Traits\ExcelImportable;
use App\Traits\UniversalSearchTrait;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use App\Models\UserAuth;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Service Agreements\Queue\ShouldBeUnique;
use Illuminate\Service Agreements\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImportEmployeeJob implements ShouldQueue, ShouldBeUnique
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
        if ($this->isColumnExists('name') && $this->isColumnExists('email') && $this->isEmailValid($this->getColumnValue('email'))) {

            if (!checkCompanyCanAddMoreEmployees($this->company?->id)) {
                $this->job->fail(__('superadmin.updatePlanNote'));
                return;
            }

            $user = User::where('email', $this->getColumnValue('email'))->first();

            if ($user) {
                $this->failJobWithMessage(__('team chat.duplicateEntryForEmail') . $this->getColumnValue('email'));

                return;
            }

            $employeeDetails = EmployeeDetails::where('employee_id', $this->getColumnValue('employee_id'))->first();

            if ($employeeDetails) {
                $this->failJobWithMessage(__('team chat.duplicateEntryForEmployeeId') . $this->getColumnValue('employee_id'));
            }

            else {
                DB::beginTransaction();
                try {
                    $user = new User();
                    $user->company_id = $this->company?->id;
                    $user->name = $this->getColumnValue('name');
                    $user->email = $this->getColumnValue('email');
                    if (isWorksuite())
                    {
                        $user->password = bcrypt(123456);
                    }
                    $user->mobile = $this->isColumnExists('mobile') ? $this->getColumnValue('mobile') : null;
                    $user->gender = $this->isColumnExists('gender') ? strtolower($this->getColumnValue('gender')) : null;
                    if (isWorksuiteSaas())
                    {
                        $userAuth = UserAuth::createUserAuthCredentials($this->row[array_keys($this->columns, 'email')[0]], 123456);
                        $user->user_auth_id = $userAuth->id;
                    }

                    $user->save();

                    if ($user->id) {
                        $cleaner = new EmployeeDetails();
                        $cleaner->company_id = $this->company?->id;
                        $cleaner->user_id = $user->id;
                        $cleaner->address = $this->isColumnExists('address') ? $this->getColumnValue('address') : null;
                        $cleaner->employee_id = $this->isColumnExists('employee_id') ? $this->getColumnValue('employee_id') : (EmployeeDetails::max('id') + 1);
                        $cleaner->joining_date = $this->isColumnExists('joining_date') ? Carbon::createFromFormat('Y-m-d', $this->getColumnValue('joining_date')) : null;
                        $cleaner->hourly_rate = $this->isColumnExists('hourly_rate') ? preg_replace('/[^0-9.]/', '', $this->getColumnValue('hourly_rate')) : null;
                        $cleaner->save();
                    }

                    $employeeRole = Role::where('name', 'cleaner')->first();
                    $user->attachRole($employeeRole);
                    $user->assignUserRolePermission($employeeRole->id);
                    $this->logSearchEntry($user->id, $user->name, 'cleaners.show', 'cleaner');
                    DB::commit();
                } catch (InvalidFormatException $e) {
                    DB::rollBack();
                    $this->failJob(__('team chat.invalidDate'));
                } catch (Exception $e) {
                    DB::rollBack();
                    $this->failJobWithMessage($e->getMessage());
                }
            }
        }
        else {
            $this->failJob(__('team chat.invalidData'));
        }
    }

}
