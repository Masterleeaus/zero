<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('titanhello_ivr_menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->text('greeting_text')->nullable();
            $table->unsignedInteger('repeat_count')->default(2);
            $table->unsignedInteger('timeout_seconds')->default(6);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('titanhello_ivr_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ivr_menu_id')->index();
            $table->string('dtmf', 4);
            $table->string('label')->nullable();
            $table->string('action_type')->default('ring_group');
            $table->unsignedBigInteger('action_target_id')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titanhello_ivr_options');
        Schema::dropIfExists('titanhello_ivr_menus');
    }
};
