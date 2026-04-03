<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('titanhello_calls', function (Blueprint $table) {
            $table->boolean('voicemail_flag')->default(false)->index();
            $table->timestamp('voicemail_received_at')->nullable()->index();
            $table->unsignedBigInteger('voicemail_recording_id')->nullable()->index();
            $table->unsignedBigInteger('voicemail_transcript_artifact_id')->nullable();
            $table->unsignedBigInteger('voicemail_summary_artifact_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('titanhello_calls', function (Blueprint $table) {
            $table->dropColumn([
                'voicemail_flag',
                'voicemail_received_at',
                'voicemail_recording_id',
                'voicemail_transcript_artifact_id',
                'voicemail_summary_artifact_id',
            ]);
        });
    }
};
