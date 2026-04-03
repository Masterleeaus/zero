<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_signers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contract_id');
            $table->string('name');
            $table->string('email');
            $table->string('role')->default('signer'); // signer, witness, approver
            $table->integer('order')->default(1);
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_text')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['contract_id','email']);
        });

        Schema::create('signature_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contract_id');
            $table->string('type'); // sent, viewed, signed, declined
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index('contract_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_events');
        Schema::dropIfExists('contract_signers');
    }
};
