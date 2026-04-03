<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add encryption flag and refactor bridge_secret storage.
     * Ensures all channel credentials are encrypted at rest using Laravel's encryption.
     */
    public function up(): void
    {
        Schema::table('omni_channel_bridges', function (Blueprint $table) {
            // Add encryption flag if not already encrypted
            $table->boolean('is_secret_encrypted')->default(false)->after('bridge_secret');
            // Add hash for validation without decryption
            $table->string('secret_hash', 64)->nullable()->after('is_secret_encrypted');
        });

        // Encrypt existing secrets (idempotent - only run once)
        DB::table('omni_channel_bridges')
            ->where('is_secret_encrypted', false)
            ->where('bridge_secret', '!=', null)
            ->each(function ($record) {
                $encrypted = encrypt($record->bridge_secret);
                $hash = hash('sha256', $record->bridge_secret);

                DB::table('omni_channel_bridges')
                    ->where('id', $record->id)
                    ->update([
                        'bridge_secret' => $encrypted,
                        'is_secret_encrypted' => true,
                        'secret_hash' => $hash,
                    ]);

                \Log::info("Encrypted bridge secret for bridge {$record->id}");
            });
    }

    public function down(): void
    {
        Schema::table('omni_channel_bridges', function (Blueprint $table) {
            $table->dropColumn('is_secret_encrypted');
            $table->dropColumn('secret_hash');
        });
    }
};
