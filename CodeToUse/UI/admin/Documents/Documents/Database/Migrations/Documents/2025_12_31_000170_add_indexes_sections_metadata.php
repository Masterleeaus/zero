<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('document_sections')) {
            Schema::table('document_sections', function (Blueprint $table) {
                if (! Schema::hasColumn('document_sections', 'document_id')) {
                    return;
                }
                // indexes already exist; keep migration idempotent
                $table->index(['document_id', 'sort_order'], 'doc_sections_doc_sort_idx');
            });
        }

        if (Schema::hasTable('document_metadata')) {
            Schema::table('document_metadata', function (Blueprint $table) {
                $table->index(['document_id', 'meta_key'], 'doc_meta_doc_key_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('document_sections')) {
            Schema::table('document_sections', function (Blueprint $table) {
                $table->dropIndex('doc_sections_doc_sort_idx');
            });
        }

        if (Schema::hasTable('document_metadata')) {
            Schema::table('document_metadata', function (Blueprint $table) {
                $table->dropIndex('doc_meta_doc_key_idx');
            });
        }
    }
};
