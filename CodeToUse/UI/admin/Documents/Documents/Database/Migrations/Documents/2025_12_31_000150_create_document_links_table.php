<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('document_links')) {
            return;
        }

        Schema::create('document_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('document_id')->index();
            $table->string('linked_type', 64);
            $table->unsignedBigInteger('linked_id');
            $table->string('label', 190)->nullable();
            $table->timestamps();

            $table->index(['linked_type', 'linked_id'], 'doc_links_target_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_links');
    }
};
