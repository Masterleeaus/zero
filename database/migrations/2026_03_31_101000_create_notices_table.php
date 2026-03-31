<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->string('title');
            $table->text('body');
            $table->string('type')->default('info'); // info, warning, urgent
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notice_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notice_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();

            $table->unique(['notice_id', 'user_id']);
            $table->foreign('notice_id')->references('id')->on('notices')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_views');
        Schema::dropIfExists('notices');
    }
};
