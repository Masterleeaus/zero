<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('documents')) {
            return;
        }

        // MySQL FULLTEXT requires InnoDB + proper collation; keep as best-effort.
        try {
            Schema::table('documents', function (Blueprint $table) {
                // Some installs might already have these indexes; ignore failures.
                $table->fullText(['title', 'content'], 'documents_fulltext_title_content');
            });
        } catch (\Throwable $e) {
            // noop
        }
    }

    public function down(): void
    {
        try {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropFullText('documents_fulltext_title_content');
            });
        } catch (\Throwable $e) {
            // noop
        }
    }
};
