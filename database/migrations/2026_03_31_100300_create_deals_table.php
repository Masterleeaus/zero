<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('title');
            $table->decimal('value', 12, 2)->default(0);
            $table->string('currency', 3)->default('AUD');
            $table->string('status')->default('open');
            $table->string('stage')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });

        Schema::create('deal_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->unsignedBigInteger('deal_id')->index();
            $table->text('body');
            $table->timestamps();

            $table->foreign('deal_id')->references('id')->on('deals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_notes');
        Schema::dropIfExists('deals');
    }
};
