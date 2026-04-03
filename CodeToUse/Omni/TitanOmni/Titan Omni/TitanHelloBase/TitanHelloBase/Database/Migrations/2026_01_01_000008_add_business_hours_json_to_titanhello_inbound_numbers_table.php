<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('titanhello_inbound_numbers', function (Blueprint $table) {
            if (!Schema::hasColumn('titanhello_inbound_numbers', 'business_hours_json')) {
                $table->json('business_hours_json')->nullable()->after('business_hours_only');
            }
        });
    }

    public function down(): void
    {
        Schema::table('titanhello_inbound_numbers', function (Blueprint $table) {
            if (Schema::hasColumn('titanhello_inbound_numbers', 'business_hours_json')) {
                $table->dropColumn('business_hours_json');
            }
        });
    }
};
