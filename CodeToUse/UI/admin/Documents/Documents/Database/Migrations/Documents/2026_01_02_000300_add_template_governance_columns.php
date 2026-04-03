<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documents_templates')) {
            return;
        }

        Schema::table('documents_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('documents_templates', 'slug')) {
                $table->string('slug')->nullable()->index();
            }
            if (!Schema::hasColumn('documents_templates', 'trade')) {
                $table->string('trade', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('documents_templates', 'role_key')) {
                $table->string('role_key', 50)->nullable()->index();
            }
            if (!Schema::hasColumn('documents_templates', 'tags')) {
                $table->text('tags')->nullable();
            }
            if (!Schema::hasColumn('documents_templates', 'published_at')) {
                $table->timestamp('published_at')->nullable()->index();
            }
            if (!Schema::hasColumn('documents_templates', 'published_by')) {
                $table->unsignedBigInteger('published_by')->nullable()->index();
            }
            if (!Schema::hasColumn('documents_templates', 'is_system')) {
                $table->boolean('is_system')->default(false)->index();
            }
        });
    }

    public function down(): void
    {
        // non-destructive rollback
    }
};
