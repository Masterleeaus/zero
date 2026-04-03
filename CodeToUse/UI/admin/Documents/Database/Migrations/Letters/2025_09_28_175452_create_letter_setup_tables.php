<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('letter_items')) {
            Schema::create('letter_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('letter_id')->nullable()->index();
                $table->string('key')->nullable();
                $table->longText('value')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_items');
    }
};
