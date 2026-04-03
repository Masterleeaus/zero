<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('document_share_links')) {
            return;
        }

        Schema::table('document_share_links', function (Blueprint $table) {
            if (!Schema::hasColumn('document_share_links', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->index();
            }
            if (!Schema::hasColumn('document_share_links', 'revoked_at')) {
                $table->timestamp('revoked_at')->nullable()->index();
            }
            if (!Schema::hasColumn('document_share_links', 'revoked_by')) {
                $table->unsignedBigInteger('revoked_by')->nullable()->index();
            }
            if (!Schema::hasColumn('document_share_links', 'max_views')) {
                $table->unsignedInteger('max_views')->nullable();
            }
            if (!Schema::hasColumn('document_share_links', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0);
            }
        });
    }

    public function down(): void
    {
        // non-destructive rollback
    }
};
