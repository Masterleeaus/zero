<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('workflow_runs')) return;

        Schema::table('workflow_runs', function (Blueprint $table) {
            if (!Schema::hasColumn('workflow_runs', 'last_error')) {
                $table->text('last_error')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Non-destructive down.
    }
};
