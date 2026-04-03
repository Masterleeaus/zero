<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('documents_templates')) {
            return;
        }

        Schema::table('documents_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('documents_templates', 'tags')) {
                $table->json('tags')->nullable();
            }
            if (!Schema::hasColumn('documents_templates', 'trade')) {
                $table->string('trade', 80)->nullable()->index();
            }
            if (!Schema::hasColumn('documents_templates', 'role_key')) {
                $table->string('role_key', 80)->nullable()->index();
            }
        });
    }

    public function down(): void {}
};
