<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titanhello_callback_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('call_id')->nullable()->index();

            $table->string('from_number')->nullable()->index();
            $table->string('to_number')->nullable()->index();

            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->string('status')->default('open')->index();     // open|done|cancelled
            $table->string('priority')->default('normal')->index(); // low|normal|high|urgent

            $table->timestamp('due_at')->nullable()->index();
            $table->text('note')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titanhello_callback_requests');
    }
};
