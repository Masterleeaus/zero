<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ext_chatbot_portal_recurring_services')) {
            return;
        }

        Schema::create('ext_chatbot_portal_recurring_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained('ext_chatbots')->cascadeOnDelete();
            $table->unsignedBigInteger('chatbot_customer_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->unsignedBigInteger('quote_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('service_name')->nullable();
            $table->string('frequency')->nullable();
            $table->date('next_service_date')->nullable();
            $table->boolean('is_paused')->default(false);
            $table->date('paused_until')->nullable();
            $table->json('extras')->nullable();
            $table->json('rules')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_chatbot_portal_recurring_services');
    }
};
