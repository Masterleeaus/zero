<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('documents')) {
            return;
        }

        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'status')) {
                $table->string('status', 50)->default('draft')->index();
            }
            if (!Schema::hasColumn('documents', 'status_changed_at')) {
                $table->timestamp('status_changed_at')->nullable()->index();
            }
            if (!Schema::hasColumn('documents', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->index();
            }
            if (!Schema::hasColumn('documents', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->index();
            }
            if (!Schema::hasColumn('documents', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        // Non-destructive down (avoid dropping columns in production)
    }
};
