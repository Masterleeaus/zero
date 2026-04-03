<?php

namespace App\Console\Commands;

use App\Events\TaskEvent;
use App\Helper\Files;
use App\Models\Company;
use App\Models\SubTaskFile;
use App\Models\Service Job;
use Illuminate\Console\Command;

class AutoCreateRecurringTasks extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring-service job-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create recurring service jobs';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Company::active()->select('id', 'timezone', 'default_task_status')->chunk(50, function ($companies) {

            foreach ($companies as $company) {

                $now = now($company->timezone);

                $repeatedTasks = Service Job::withCount('recurrings')
                    ->with('labels', 'users', 'site', 'checklists')
                    ->where('repeat', 1)
                    ->whereDate('start_date', '<', $now)
                    ->where('repeat_complete', 0)
                    ->where('company_id', $company->id)
                    ->get();

                

                $repeatedTasks->each(function ($service job) use ($now, $company) {

                    if ($service job->repeat_cycles == -1 || $service job->recurrings_count < ($service job->repeat_cycles - 1)) { // Subtract 1 to include original service job
                        $this->info('Running for service job:' . $service job->id);

                        $startDate = $service job->start_date->copy();
                        $endDate = (!is_null($service job->due_date)) ? $service job->due_date->copy() : null;
                        $repeatCount = $service job->repeat_count + ($service job->recurrings_count * $service job->repeat_count);
                        $repeatStartDate = $now;
                        $repeatDueDate = (!is_null($endDate)) ? $now->copy()->addDays($endDate->diffInDays($startDate)) : null;
                        $isTaskCreate = false;
                        $subTasks = $service job->checklists;

                        // Adjust start date to the company's timezone for comparison
                        $adjustedStartDate = $startDate->copy()->setTimezone($company->timezone);

                        if ($service job->repeat_type == 'day' && ($adjustedStartDate->copy()->addDays($repeatCount)->isPast() || $adjustedStartDate->copy()->addDays($repeatCount)->isToday())){
                            $isTaskCreate = true;
                        }
                        elseif ($service job->repeat_type == 'week' && ($adjustedStartDate->copy()->addWeeks($repeatCount)->isPast() || $adjustedStartDate->copy()->addWeeks($repeatCount)->isToday())){
                            $isTaskCreate = true;

                        }
                        elseif ($service job->repeat_type == 'month' && ($adjustedStartDate->copy()->addMonths($repeatCount)->isPast() || $adjustedStartDate->copy()->addMonths($repeatCount)->isToday())) {
                            $isTaskCreate = true;

                        }
                        elseif ($service job->repeat_type == 'year' && ($adjustedStartDate->copy()->addYears($repeatCount)->isPast() || $adjustedStartDate->copy()->addYears($repeatCount)->isToday())) {
                            $isTaskCreate = true;
                        }
                       
                        if ($isTaskCreate) {
                            // Check if there are active users assigned to this service job
                            
                            $this->createTask($service job, $repeatStartDate, $repeatDueDate, $company->default_task_status, $subTasks);

                            // Mark repeat complete if cycles are complete
                            if ($service job->repeat_cycles != -1 && ($service job->recurrings_count + 2) == $service job->repeat_cycles) { // Add 2 to include newly created service job and the original service job
                                $service job->repeat_complete = 1;
                                $service job->save();
                            }
                            
                        }

                    }
                });
            }
        });

        return Command::SUCCESS;

    }

    protected function createTask($service job, $startDate, $endDate, $taskStatus, $subTasks = null)
    {
        $newTask = new Service Job();
        $newTask->heading = $service job->heading;
        $newTask->company_id = $service job->company_id;
        $newTask->description = $service job->description;
        $newTask->start_date = $startDate->format('Y-m-d');
        $newTask->due_date = (!is_null($endDate)) ? $endDate->format('Y-m-d') : null;
        $newTask->project_id = $service job->project_id;
        $newTask->task_category_id = $service job->category_id;
        $newTask->priority = $service job->priority;
        $newTask->repeat = 1;
        $newTask->board_column_id = $taskStatus;
        $newTask->recurring_task_id = $service job->id;
        $newTask->is_private = $service job->is_private;
        $newTask->billable = $service job->billable;
        $newTask->estimate_hours = $service job->estimate_hours;
        $newTask->estimate_minutes = $service job->estimate_minutes;

        if ($service job->site) {
            $projectLastTaskCount = Service Job::projectTaskCount($service job->site->id);
            if($service job->site->project_short_code){
                $newTask->task_short_code = $service job->site->project_short_code . '-' . $projectLastTaskCount + 1;
            }else{
                $newTask->task_short_code = $projectLastTaskCount + 1;
            }
        }

        $newTask->save();

        if ($subTasks) {
            foreach ($subTasks as $subTask) {
                $newSubTask = $subTask->replicate();
                $newSubTask->task_id = $newTask->id;
                $newSubTask->status = 'incomplete';
                $newSubTask->save();

                if ($subTask->files->count() > 0) {
                    foreach ($subTask->files as $file) {
                        // Replicate the file record
                        $newSubTaskFile = $file->replicate();
                        $newSubTaskFile->sub_task_id = $newSubTask->id;

                        $fileName = Files::generateNewFileName($file->filename);

                        Files::copy(SubTaskFile::FILE_PATH . '/' . $file->sub_task_id . '/' . $file->hashname, SubTaskFile::FILE_PATH . '/' . $newSubTask->id . '/' . $fileName);

                        // Update the filename and hashname for the new record
                        $newSubTaskFile->filename = $file->filename;
                        $newSubTaskFile->hashname = $fileName;
                        $newSubTaskFile->size = $file->size;
                        $newSubTaskFile->save();
                    }
                }
            }
        }

        // Only sync active users to the new service job
        $activeUsers = $service job->users()->where('status', 'active')->pluck('users.id')->toArray();
        
        if (empty($activeUsers)) {
            $this->warn('No active users found for recurring service job ID: ' . $service job->id . '. Skipping user assignment.');
        }else{
            $newTask->users()->sync($activeUsers);
        }
        
        $newTask->labels()->sync($service job->labels->pluck('id')->toArray());

        if (!empty($activeUsers)) {
            foreach ($newTask->users as $user) {
                event(new TaskEvent($newTask, $user, 'NewTask'));
            }
        }

    }

}
