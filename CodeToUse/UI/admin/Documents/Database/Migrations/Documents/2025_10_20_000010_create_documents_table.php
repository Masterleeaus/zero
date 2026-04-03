<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('title');
            $table->string('type')->default('general');
            $table->string('category')->nullable();
            $table->string('template_slug')->nullable();
            $table->longText('body_markdown')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('status')->default('draft');
            $table->string('qr_slug')->nullable();
            $table->timestamps();
        });
        }
    }
    public function down(): void { Schema::dropIfExists('documents'); }
};
