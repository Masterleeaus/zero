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
            if (!Schema::hasColumn('workflow_runs', 'idempotency_key')) {
                $table->string('idempotency_key', 64)->nullable()->index();
            }
            if (!Schema::hasColumn('workflow_runs', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        // Non-destructive down.
    }
};
