<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // documents
        if (Schema::hasTable('documents') && !Schema::hasColumn('documents', 'position')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->unsignedInteger('position')->nullable()->after('subcategory');
                $table->index(['tenant_id', 'position'], 'idx_documents_tenant_position');
            });
        }

        // document_folders
        if (Schema::hasTable('document_folders') && !Schema::hasColumn('document_folders', 'position')) {
            Schema::table('document_folders', function (Blueprint $table) {
                $table->unsignedInteger('position')->nullable()->after('parent_id');
                $table->index(['tenant_id', 'position'], 'idx_doc_folders_tenant_position');
            });
        }

        // documents_templates
        if (Schema::hasTable('documents_templates') && !Schema::hasColumn('documents_templates', 'position')) {
            Schema::table('documents_templates', function (Blueprint $table) {
                $table->unsignedInteger('position')->nullable()->after('subcategory');
                $table->index(['tenant_id', 'position'], 'idx_doc_templates_tenant_position');
            });
        }
    }

    public function down(): void
    {
        // leave columns in place (non-destructive down); safe rollback not required for production upgrades
    }
};
