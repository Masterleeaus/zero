<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contract_id');
            $table->integer('version')->default(1);
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->string('hash')->nullable();
            $table->timestamps();
            $table->index('contract_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_versions');
    }
};
