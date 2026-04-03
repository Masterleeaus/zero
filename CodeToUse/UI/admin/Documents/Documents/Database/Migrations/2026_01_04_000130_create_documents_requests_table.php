<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documents_requests')) {
            Schema::create('documents_requests', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tenant_id')->index();
                $table->unsignedBigInteger('requested_by')->nullable()->index();
                $table->unsignedBigInteger('document_id')->nullable()->index(); // optional link to created/received doc
                $table->string('title');
                $table->string('recipient_email')->nullable()->index();
                $table->string('recipient_name')->nullable();
                $table->text('message')->nullable();
                $table->dateTime('due_at')->nullable()->index();
                $table->string('status')->default('requested')->index(); // requested, received, overdue, cancelled
                $table->string('token', 80)->unique();
                $table->dateTime('sent_at')->nullable();
                $table->dateTime('received_at')->nullable();
                $table->dateTime('cancelled_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('documents_requests')) {
            Schema::drop('documents_requests');
        }
    }
};
