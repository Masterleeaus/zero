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

        if (!Schema::hasTable('document_share_link_hits')) {
            Schema::create('document_share_link_hits', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('share_link_id')->index();
                $table->unsignedBigInteger('document_id')->nullable()->index();
                $table->unsignedBigInteger('tenant_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('ip', 64)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('viewed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // non-destructive rollback
    }
};
