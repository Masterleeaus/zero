<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_area_districts', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('region_id')->nullable()->index();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('manager_user_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('region_id')
                ->references('id')
                ->on('service_area_regions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_area_districts');
    }
};
