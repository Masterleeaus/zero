<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('builder_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('builder_sections')->cascadeOnDelete();
            $table->string('key'); // title, subtitle, image, button_text, etc.
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['section_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('builder_contents');
    }
};
