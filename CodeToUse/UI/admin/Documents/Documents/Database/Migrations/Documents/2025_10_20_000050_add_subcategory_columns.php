<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('documents_templates') && !Schema::hasColumn('documents_templates', 'subcategory')) {
            Schema::table('documents_templates', function (Blueprint $table) {
                $table->string('subcategory')->nullable()->index();
            });
        }
        if (Schema::hasTable('documents') && !Schema::hasColumn('documents', 'subcategory')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->string('subcategory')->nullable()->index();
            });
        }
    }
    public function down(): void {
        if (Schema::hasTable('documents_templates') && Schema::hasColumn('documents_templates', 'subcategory')) {
            Schema::table('documents_templates', function (Blueprint $table) {
                $table->dropColumn('subcategory');
            });
        }
        if (Schema::hasTable('documents') && Schema::hasColumn('documents', 'subcategory')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropColumn('subcategory');
            });
        }
    }
};
