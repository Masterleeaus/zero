<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('document_status_histories')) {
            return;
        }

        Schema::create('document_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('document_id')->index();
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50)->index();
            $table->unsignedBigInteger('changed_by')->nullable()->index();
            $table->text('note')->nullable();
            $table->timestamp('changed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['tenant_id','document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_status_histories');
    }
};
