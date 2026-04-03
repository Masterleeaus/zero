<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documents_document_tag')) {
            Schema::create('documents_document_tag', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tenant_id')->index();
                $table->unsignedBigInteger('document_id')->index();
                $table->unsignedBigInteger('tag_id')->index();
                $table->timestamps();

                $table->unique(['tenant_id', 'document_id', 'tag_id'], 'uniq_docs_doc_tag');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('documents_document_tag')) {
            Schema::drop('documents_document_tag');
        }
    }
};
