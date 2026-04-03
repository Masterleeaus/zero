<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('documents')) {
            return;
        }

        // MySQL fulltext is optional; guard hard.
        try {
            $driver = DB::connection()->getDriverName();
        } catch (Throwable $e) {
            return;
        }

        if ($driver !== 'mysql') {
            return;
        }

        try {
            DB::statement('ALTER TABLE `documents` ADD FULLTEXT `documents_fulltext_title_body` (`title`, `body_markdown`)');
        } catch (Throwable $e) {
            // Ignore if index exists or unsupported.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('documents')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE `documents` DROP INDEX `documents_fulltext_title_body`');
        } catch (Throwable $e) {
        }
    }
};
