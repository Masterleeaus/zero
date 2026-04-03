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
            $table->string('linked_type', 64)->index(); // jobsite/job/quote/invoice
            $table->unsignedBigInteger('linked_id')->index();
            $table->string('label', 191)->nullable();
            $table->timestamps();

            $table->unique(['tenant_id','document_id','linked_type','linked_id'], 'doc_links_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_links');
    }
};
