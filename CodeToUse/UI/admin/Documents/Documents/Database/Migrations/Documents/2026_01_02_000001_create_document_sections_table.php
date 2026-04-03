<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('document_sections')) {
            return;
        }

        Schema::create('document_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('document_id')->index();
            $table->string('key', 50)->nullable();
            $table->string('title', 191)->nullable();
            $table->longText('body_markdown')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tenant_id','document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sections');
    }
};
