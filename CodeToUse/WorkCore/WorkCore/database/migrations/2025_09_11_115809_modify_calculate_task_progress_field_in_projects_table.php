<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Site;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // get the ids of the sites where calculate_task_progress is true
        $projectstrue = Site::where('calculate_task_progress', 'true')->get(); 
        $projectsfalse = Site::where('calculate_task_progress', 'false')->get();

        // Use raw SQL to modify the enum column
        DB::statement("ALTER TABLE sites MODIFY COLUMN calculate_task_progress ENUM('manual', 'task_completion', 'project_total_time', 'project_deadline') DEFAULT 'manual'");

        foreach ($projectstrue as $site) {
            $site->calculate_task_progress = 'task_completion';
            $site->save();
        }

        foreach ($projectsfalse as $site) {
            $site->calculate_task_progress = 'manual';
            $site->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original enum values
        DB::statement("ALTER TABLE sites MODIFY COLUMN calculate_task_progress ENUM('true', 'false') DEFAULT 'true'");
    }
};