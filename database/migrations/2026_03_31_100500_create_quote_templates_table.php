<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('currency', 3)->default('AUD');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('quote_template_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('quote_template_id')->index();
            $table->text('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('quote_template_id')->references('id')->on('quote_templates')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_template_items');
        Schema::dropIfExists('quote_templates');
    }
};
