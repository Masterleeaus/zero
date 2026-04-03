<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titanhello_call_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('call_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('note');
            $table->timestamps();

            $table->index('call_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titanhello_call_notes');
    }
};
