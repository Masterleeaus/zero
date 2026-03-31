<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('service_issues')) {
            Schema::create('service_issues', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index()->nullable();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('assigned_to')->nullable()->index();
                $table->string('subject');
                $table->text('description')->nullable();
                $table->string('status')->default('open')->index();
                $table->string('priority')->default('medium')->index();
                $table->string('source')->nullable(); // email intake compatibility
                $table->string('external_reference')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                    $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
                }
            });
        }

        if (! Schema::hasTable('service_issue_messages')) {
            Schema::create('service_issue_messages', static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_issue_id')->index();
                $table->unsignedBigInteger('company_id')->index()->nullable();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->boolean('is_internal')->default(false)->index();
                $table->text('message')->nullable();
                $table->json('attachments')->nullable();
                $table->timestamps();

                $table->foreign('service_issue_id')->references('id')->on('service_issues')->cascadeOnDelete();

                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_issue_messages');
        Schema::dropIfExists('service_issues');
    }
};
