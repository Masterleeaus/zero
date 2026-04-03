<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('documents')) {
            return;
        }

        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'effective_at')) {
                $table->timestamp('effective_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('documents', 'review_at')) {
                $table->timestamp('review_at')->nullable()->after('effective_at');
            }
            if (! Schema::hasColumn('documents', 'trade')) {
                $table->string('trade', 64)->nullable()->after('review_at');
            }
            if (! Schema::hasColumn('documents', 'role')) {
                $table->string('role', 64)->nullable()->after('trade');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('documents')) {
            return;
        }

        Schema::table('documents', function (Blueprint $table) {
            foreach (['role', 'trade', 'review_at', 'effective_at'] as $col) {
                if (Schema::hasColumn('documents', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
