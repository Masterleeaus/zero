<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Service Job;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        $service jobs = Service Job::whereNull('task_short_code')->whereNotNull('project_id')->get();

        foreach ($service jobs as $service job) {

            try{
                $site = $service job->site;
                $service job->task_short_code = $site->project_short_code . '-' . $service job->id;
                $service job->saveQuietly();
            }catch(\Exception $e){}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
