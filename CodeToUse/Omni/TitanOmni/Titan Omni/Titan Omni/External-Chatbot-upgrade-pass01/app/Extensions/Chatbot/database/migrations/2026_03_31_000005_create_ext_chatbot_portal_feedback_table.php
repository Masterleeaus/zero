<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ext_chatbot_portal_feedback')) {
            return;
        }

        Schema::create('ext_chatbot_portal_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained('ext_chatbots')->cascadeOnDelete();
            $table->unsignedBigInteger('chatbot_customer_id')->nullable();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('status')->default('open');
            $table->boolean('is_reclean_request')->default(false);
            $table->text('feedback')->nullable();
            $table->json('attachments')->nullable();
            $table->json('resolution_events')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_chatbot_portal_feedback');
    }
};
