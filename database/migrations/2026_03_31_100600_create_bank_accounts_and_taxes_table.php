<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('account_name');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bsb')->nullable();
            $table->string('currency', 3)->default('AUD');
            $table->boolean('is_default')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->decimal('rate', 5, 2);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_compound')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('bank_accounts');
    }
};
