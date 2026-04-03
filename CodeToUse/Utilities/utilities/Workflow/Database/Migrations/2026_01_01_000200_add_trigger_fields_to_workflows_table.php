<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('workflows')) {
            Schema::table('workflows', function (Blueprint $table) {
                if (!Schema::hasColumn('workflows', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('workflow_data');
                }
                if (!Schema::hasColumn('workflows', 'trigger_event')) {
                    $table->string('trigger_event', 191)->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('workflows', 'trigger_conditions')) {
                    $table->json('trigger_conditions')->nullable()->after('trigger_event');
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive: do not drop columns automatically.
    }
};
