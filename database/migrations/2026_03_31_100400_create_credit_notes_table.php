<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->string('reference')->nullable();
            $table->string('title');
            $table->string('status')->default('draft');
            $table->string('currency', 3)->default('AUD');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->date('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('credit_note_id')->index();
            $table->text('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('credit_note_id')->references('id')->on('credit_notes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_items');
        Schema::dropIfExists('credit_notes');
    }
};
