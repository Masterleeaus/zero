<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('document_versions')) {
            return;
        }

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('document_id')->index();
            $table->unsignedInteger('version_no')->default(1);
            $table->string('reason', 100)->nullable();
            $table->string('version_hash', 64)->nullable()->index();
            $table->json('snapshot')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->unique(['tenant_id','document_id','version_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
