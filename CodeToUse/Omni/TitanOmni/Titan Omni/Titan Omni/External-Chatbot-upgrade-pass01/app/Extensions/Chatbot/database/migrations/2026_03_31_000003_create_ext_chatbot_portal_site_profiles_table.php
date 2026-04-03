<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ext_chatbot_portal_site_profiles')) {
            return;
        }

        Schema::create('ext_chatbot_portal_site_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained('ext_chatbots')->cascadeOnDelete();
            $table->unsignedBigInteger('chatbot_customer_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('site_label')->nullable();
            $table->string('entry_method')->nullable();
            $table->text('access_notes')->nullable();
            $table->text('parking_notes')->nullable();
            $table->text('pet_notes')->nullable();
            $table->text('priority_rooms')->nullable();
            $table->json('preferences')->nullable();
            $table->json('media')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ext_chatbot_portal_site_profiles');
    }
};
