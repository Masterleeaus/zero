<?php

use App\Models\Company;
use App\Models\ProjectTimeLog;
use App\Models\Service Job;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add project_id in project_time_logs where missing

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            $service jobs = Service Job::whereNotNull('project_id')->whereHas('timeLogged')->where('company_id', $company->id)->select('id', 'project_id')->get();

            foreach($service jobs as $service job) {
                ProjectTimeLog::where('task_id', $service job->id)->update(['project_id' => $service job->project_id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

};
