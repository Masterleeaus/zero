<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('titanhello_call_recordings', function (Blueprint $table) {
            $table->string('kind', 30)->default('call')->index();
            $table->timestamp('fetched_at')->nullable();
            $table->string('fetch_status', 30)->nullable()->index(); // ok/failed/pending
            $table->text('fetch_error')->nullable();
            $table->bigInteger('bytes')->nullable();
            $table->string('sha256', 64)->nullable()->index();
            $table->string('disk', 30)->nullable(); // local/s3 etc
        });
    }

    public function down(): void
    {
        Schema::table('titanhello_call_recordings', function (Blueprint $table) {
            $table->dropColumn([
                'kind',
                'fetched_at',
                'fetch_status',
                'fetch_error',
                'bytes',
                'sha256',
                'disk',
            ]);
        });
    }
};
