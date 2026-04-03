<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('omni_knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->string('title');
            $table->string('source_type', 50)->default('text')->index();
            $table->string('source_ref')->nullable();
            $table->longText('content')->nullable();
            $table->text('summary')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('omni_knowledge_articles');
    }
};
