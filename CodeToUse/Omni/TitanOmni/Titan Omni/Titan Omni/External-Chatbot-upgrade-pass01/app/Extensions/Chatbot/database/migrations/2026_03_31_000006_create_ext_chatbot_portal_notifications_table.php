<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ext_chatbot_portal_notifications')) {
            return;
        }

        Schema::create('ext_chatbot_portal_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained('ext_chatbots')->cascadeOnDelete();
            $table->unsignedBigInteger('chatbot_customer_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('type')->default('info');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('action_label')->nullable();
            $table->string('action_url')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_chatbot_portal_notifications');
    }
};
