<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('document_share_links')) {
            return;
        }

        Schema::create('document_share_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('document_id')->index();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_share_links');
    }
};
