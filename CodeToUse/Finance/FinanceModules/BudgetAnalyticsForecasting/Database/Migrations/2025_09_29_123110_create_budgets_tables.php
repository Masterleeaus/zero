<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('budgets')) {
            Schema::create('budgets', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');
                $table->string('category')->nullable();
                $table->decimal('amount', 12, 2)->default(0);
                $table->timestamps();
                $table->index(['user_id']);
            });
        }

        if (!Schema::hasTable('budget_items')) {
            Schema::create('budget_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('budget_id');
                $table->date('month');
                $table->decimal('amount', 12, 2)->default(0);
                $table->timestamps();
                $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
                $table->index(['budget_id', 'month']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('budget_items')) Schema::dropIfExists('budget_items');
        if (Schema::hasTable('budgets')) Schema::dropIfExists('budgets');
    }
};
