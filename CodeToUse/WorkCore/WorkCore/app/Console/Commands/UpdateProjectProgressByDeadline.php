<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Traits\ProjectProgress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateProjectProgressByDeadline extends Command
{
    use ProjectProgress;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sites-update-deadline-progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update site progress for sites using deadline-based calculation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting site progress update for deadline-based sites...');

        // Get all sites that use deadline-based progress calculation
        $sites = Site::where('calculate_task_progress', 'project_deadline')
            ->whereNotNull('start_date')
            ->whereNotNull('deadline')
            ->get();

        $updatedCount = 0;
        $errorCount = 0;

        foreach ($sites as $site) {
            try {
                $oldProgress = $site->completion_percent;
                $oldStatus = $site->status;

                // Calculate and update progress
                $newProgress = $this->calculateProjectProgressByDeadline($site->id);

                if ($newProgress !== false) {
                    // Reload the site to get updated values
                    $site->refresh();
                    
                    $newStatus = $site->status;
                    
                    // Log significant changes
                    if ($oldProgress != $newProgress || $oldStatus != $newStatus) {
                        Log::info("Site progress updated", [
                            'project_id' => $site->id,
                            'project_name' => $site->project_name,
                            'old_progress' => $oldProgress,
                            'new_progress' => $newProgress,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                            'deadline' => $site->deadline->format('Y-m-d')
                        ]);
                        
                        $this->line("Updated site: {$site->project_name} - Progress: {$oldProgress}% → {$newProgress}%");
                        
                        if ($oldStatus != $newStatus) {
                            $this->line("  Status changed: {$oldStatus} → {$newStatus}");
                        }
                    }
                    
                    $updatedCount++;
                } else {
                    $this->warn("Failed to calculate progress for site: {$site->project_name} (ID: {$site->id})");
                    $errorCount++;
                }
            } catch (\Exception $e) {
                Log::error("Error updating site progress", [
                    'project_id' => $site->id,
                    'project_name' => $site->project_name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $this->error("Error updating site {$site->project_name}: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->info("Site progress update completed.");
        $this->info("Sites processed: " . $sites->count());
        $this->info("Successfully updated: {$updatedCount}");
        
        if ($errorCount > 0) {
            $this->warn("Errors encountered: {$errorCount}");
        }

        // Log summary
        Log::info("Daily site progress update completed", [
            'total_projects' => $sites->count(),
            'updated_count' => $updatedCount,
            'error_count' => $errorCount,
            'execution_time' => now()->toDateTimeString()
        ]);

        return Command::SUCCESS;
    }
}
