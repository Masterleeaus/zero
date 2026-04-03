<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documents_request_uploads')) {
            Schema::create('documents_request_uploads', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tenant_id')->index();
                $table->unsignedBigInteger('request_id')->index();
                $table->unsignedBigInteger('document_file_id')->nullable()->index();
                $table->string('original_name')->nullable();
                $table->string('path')->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->string('mime')->nullable();
                $table->string('ip')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('documents_request_uploads')) {
            Schema::drop('documents_request_uploads');
        }
    }
};
