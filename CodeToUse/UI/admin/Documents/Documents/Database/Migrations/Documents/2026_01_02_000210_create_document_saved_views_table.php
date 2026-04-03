<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('document_saved_views')) {
            return;
        }

        Schema::create('document_saved_views', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name', 100);
            $table->json('filters')->nullable();
            $table->boolean('is_system')->default(false)->index();
            $table->timestamps();

            $table->index(['tenant_id','user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_saved_views');
    }
};
