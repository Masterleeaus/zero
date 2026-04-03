<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('document_sections')) {
            return;
        }

        Schema::table('document_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('document_sections', 'key')) {
                $table->string('key', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('document_sections', 'position')) {
                $table->unsignedInteger('position')->default(0)->index();
            }
            if (!Schema::hasColumn('document_sections', 'format')) {
                $table->string('format', 20)->default('markdown');
            }
        });
    }

    public function down(): void {}
};
