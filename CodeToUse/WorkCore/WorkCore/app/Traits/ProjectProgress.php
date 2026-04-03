<?php

/**
 * Created by PhpStorm.
 * User: DEXTER
 * Date: 13/07/17
 * Time: 4:53 PM
 */

namespace App\Traits;

use App\Models\Site;
use App\Models\Service Job;
use App\Models\TaskboardColumn;
use App\Models\ProjectTimeLog;
use Carbon\Carbon;

trait ProjectProgress
{

    public function calculateProjectProgress($projectId, $projectProgress = 'false')
    {
        $site = Site::withTrashed()->findOrFail($projectId);

        if (!is_null($site) && ($site->calculate_task_progress == 'task_completion' || $projectProgress == 'true')) {
            $taskBoardColumn = TaskboardColumn::completeColumn();
           
            if (is_null($projectId)) {
                return false;
            }
           
            $totalTasks = Service Job::where('project_id', $projectId)->whereNull('deleted_at')->count();

            if ($totalTasks == 0) {
                info('totalTasks is 0');
                $percentComplete = 0;
                $site->completion_percent = $percentComplete;
           
            
                if ($site->calculate_task_progress == 'task_completion') {
                    
                    if($percentComplete == 100){
                        $site->status = 'finished';
                    }else if($percentComplete < 100 && $site->status == 'finished'){
                        $site->status = 'in progress';
                    }
                }

                $site->save();

                
                return '0';
            }
           
            $completedTasks = Service Job::where('project_id', $projectId)->whereNull('deleted_at')
                ->where('service jobs.board_column_id', $taskBoardColumn->id)
                ->whereNull('deleted_at')
                ->count();
           
            $percentComplete = ($completedTasks / $totalTasks) * 100;
            
            $site->completion_percent = $percentComplete;

            // Update site status based on completion
            if ($percentComplete >= 100) {
                $site->status = 'finished';
            } elseif ($site->status == 'finished' && $percentComplete < 100) {
                $site->status = 'in progress';
            }
            
            $site->save();

            return $percentComplete;
        
        }
    }

    /**
     * Calculate site progress based on total time allocated vs time spent
     * 
     * @param int $projectId
     * @return float|false
     */
    public function calculateProjectProgressByTime($projectId)
    {
        $site = Site::withTrashed()->findOrFail($projectId);

        if (is_null($site) || $site->calculate_task_progress != 'project_total_time') {
            
            return false;
        }

        // Get total allocated hours for the site
        $totalAllocatedHours = $site->hours_allocated;
        
        if (is_null($totalAllocatedHours) || $totalAllocatedHours <= 0) {
            return false;
        }

        // Get total time spent on site (in minutes) - including active timers
        $totalTimeSpentMinutes = ProjectTimeLog::where('project_id', $projectId)
            ->whereHas('service job', function ($query) {
                $query->whereNull('deleted_at'); // exclude soft-deleted service jobs
            })
            ->get()
            ->sum(function ($timeLog) {
                if (is_null($timeLog->end_time)) {
                    // For active timers, calculate current elapsed time
                    $startTime = $timeLog->start_time;
                    $currentTime = now();
                    
                    // Subtract any break time
                    $breakMinutes = $timeLog->breaks->sum('total_minutes');
                    $activeBreakMinutes = 0;
                    
                    if ($timeLog->activeBreak) {
                        $activeBreakMinutes = $timeLog->activeBreak->start_time->diffInMinutes($currentTime);
                    }
                    
                    $totalBreakMinutes = $breakMinutes + $activeBreakMinutes;
                    $elapsedMinutes = $startTime->diffInMinutes($currentTime) - $totalBreakMinutes;
                    
                    return max(0, $elapsedMinutes);
                } else {
                    // For completed timers, use the stored total_minutes
                    return $timeLog->total_minutes;
                }
            });

       

        // Convert allocated hours to minutes
        $totalAllocatedMinutes = $totalAllocatedHours * 60;
       
        // Calculate progress percentage
        $percentComplete = ($totalTimeSpentMinutes / $totalAllocatedMinutes) * 100;
       
        
        // Ensure we have a valid numeric value
        $percentComplete = (float) $percentComplete;
        
        // Cap at 100%
        if ($percentComplete > 100) {
            $percentComplete = 100;
        }
        
        // Ensure minimum of 0
        if ($percentComplete < 0) {
            $percentComplete = 0;
        }

        // Update site completion percentage
        $site->completion_percent = $percentComplete;
        
        // Update site status based on completion
        if ($percentComplete >= 100) {
            $site->status = 'finished';
        } elseif ($site->status == 'finished' && $percentComplete < 100) {
            $site->status = 'in progress';
        }

        $site->save();

        // If we're in a controller context, stop timers when time limit is reached
        if (method_exists($this, 'stopProjectTimersOnTimeLimit') && $percentComplete >= 100) {
            $this->stopProjectTimersOnTimeLimit($projectId);
        }

        return $percentComplete;
    }

    /**
     * Get remaining time for a site in minutes
     * 
     * @param int $projectId
     * @return float|false
     */
    public function getProjectRemainingTime($projectId)
    {
        $site = Site::withTrashed()->findOrFail($projectId);

        if (is_null($site) || $site->calculate_task_progress != 'project_total_time') {
            return false;
        }

        // Get total allocated hours for the site
        $totalAllocatedHours = $site->hours_allocated;
        
        if (is_null($totalAllocatedHours) || $totalAllocatedHours <= 0) {
            return false;
        }

        // Get total time spent on site (in minutes) - including active timers
        $totalTimeSpentMinutes = ProjectTimeLog::where('project_id', $projectId)
            ->whereHas('service job', function ($query) {
                $query->whereNull('deleted_at'); // exclude soft-deleted service jobs
            })
            ->get()
            ->sum(function ($timeLog) {
                if (is_null($timeLog->end_time)) {
                    // For active timers, calculate current elapsed time
                    $startTime = $timeLog->start_time;
                    $currentTime = now();
                    
                    // Subtract any break time
                    $breakMinutes = $timeLog->breaks->sum('total_minutes');
                    $activeBreakMinutes = 0;
                    
                    if ($timeLog->activeBreak) {
                        $activeBreakMinutes = $timeLog->activeBreak->start_time->diffInMinutes($currentTime);
                    }
                    
                    $totalBreakMinutes = $breakMinutes + $activeBreakMinutes;
                    $elapsedMinutes = $startTime->diffInMinutes($currentTime) - $totalBreakMinutes;
                    
                    return max(0, $elapsedMinutes);
                } else {
                    // For completed timers, use the stored total_minutes
                    return $timeLog->total_minutes;
                }
            });

        // Convert allocated hours to minutes
        $totalAllocatedMinutes = $totalAllocatedHours * 60;

        // Calculate remaining time in minutes
        $remainingMinutes = $totalAllocatedMinutes - $totalTimeSpentMinutes;

        return max(0, $remainingMinutes); // Return 0 if negative
    }

    /**
     * Check if site has remaining time
     * 
     * @param int $projectId
     * @return bool
     */
    public function hasProjectRemainingTime($projectId)
    {
        $remainingTime = $this->getProjectRemainingTime($projectId);
        return $remainingTime !== false && $remainingTime > 0;
    }

    /**
     * Calculate site progress based on site deadline
     * 
     * @param int $projectId
     * @return float|false
     */
    public function calculateProjectProgressByDeadline($projectId)
    {
        $site = Site::withTrashed()->findOrFail($projectId);

        if (is_null($site) || $site->calculate_task_progress != 'project_deadline') {
            return false;
        }

        // Check if both start date and deadline are set
        if (is_null($site->start_date) || is_null($site->deadline)) {
            return false;
        }

        $startDate = $site->start_date;
        // Add one day to deadline so 100% completion happens the day after deadline
        // This ensures the site reaches 100% on the day after the deadline date
        $deadline = $site->deadline->copy()->addDay();
        
        $currentDate = now();

        // Calculate total site duration in days
        $totalDuration = $startDate->diffInDays($deadline);
        

        if ($totalDuration <= 0) {
            // If deadline is same as or before start date, set to 100%
            $percentComplete = 100;
        } else {
            // Calculate elapsed time in days
            $elapsedDays = $startDate->diffInDays($currentDate);
            
            // Calculate progress percentage
            $percentComplete = ($elapsedDays / $totalDuration) * 100;
            
            // Cap at 100%
            if ($percentComplete > 100) {
                $percentComplete = 100;
            }
            
            // Ensure minimum of 0
            if ($percentComplete < 0) {
                $percentComplete = 0;
            }
        }

        // Ensure we have a valid numeric value
        $percentComplete = (float) $percentComplete;

        // Update site completion percentage
        $site->completion_percent = $percentComplete;
        
        // Update site status based on completion
        if ($percentComplete >= 100) {
            $site->status = 'finished';
        } elseif ($site->status == 'finished' && $percentComplete < 100) {
            $site->status = 'in progress';
        }

        $site->save();

        return $percentComplete;
    }

}
