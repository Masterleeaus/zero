<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('workflows')) return;

        Schema::table('workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('workflows', 'rate_limit_max')) {
                $table->unsignedInteger('rate_limit_max')->nullable();
            }
            if (!Schema::hasColumn('workflows', 'rate_limit_seconds')) {
                $table->unsignedInteger('rate_limit_seconds')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Non-destructive down.
    }
};
