<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extend ext_chatbot_channels for SMS and Voice credentials
        if (Schema::hasTable('ext_chatbot_channels')) {
            Schema::table('ext_chatbot_channels', function (Blueprint $table) {
                // Add channel type if not exists
                if (!Schema::hasColumn('ext_chatbot_channels', 'channel_type')) {
                    $table->enum('channel_type', ['whatsapp', 'sms', 'voice'])
                        ->default('whatsapp')
                        ->after('id');
                }

                // Note: 'credentials' column should already exist from WhatsApp
                // It stores all provider credentials as JSON:
                // {
                //   "whatsapp_sid": "...",
                //   "whatsapp_token": "...",
                //   "sms_sid": "...",
                //   "sms_token": "...",
                //   "sms_phone": "...",
                //   "voice_sid": "...",
                //   "voice_token": "...",
                //   "voice_phone": "..."
                // }
            });
        }

        // Extend ext_chatbot_conversations for voice call tracking
        if (Schema::hasTable('ext_chatbot_conversations')) {
            Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
                // Voice call specific fields
                if (!Schema::hasColumn('ext_chatbot_conversations', 'call_phone_number')) {
                    $table->string('call_phone_number')->nullable()->after('session_id');
                }

                if (!Schema::hasColumn('ext_chatbot_conversations', 'call_status')) {
                    $table->enum('call_status', ['incoming', 'ringing', 'connected', 'transferred', 'ended'])
                        ->nullable()
                        ->after('call_phone_number');
                }

                if (!Schema::hasColumn('ext_chatbot_conversations', 'call_started_at')) {
                    $table->timestamp('call_started_at')->nullable()->after('call_status');
                }

                if (!Schema::hasColumn('ext_chatbot_conversations', 'call_ended_at')) {
                    $table->timestamp('call_ended_at')->nullable()->after('call_started_at');
                }

                if (!Schema::hasColumn('ext_chatbot_conversations', 'call_duration_seconds')) {
                    $table->integer('call_duration_seconds')->default(0)->after('call_ended_at');
                }
            });
        }

        // Extend ext_chatbot_histories for voice transcript tracking
        if (Schema::hasTable('ext_chatbot_histories')) {
            Schema::table('ext_chatbot_histories', function (Blueprint $table) {
                // message_type should already exist, but ensure it can store voice types
                // Possible values: 'text', 'voice_transcript_user', 'voice_transcript_assistant',
                // 'voice_call_started', 'voice_call_ended', 'voice_call_recording'
                
                if (!Schema::hasColumn('ext_chatbot_histories', 'voice_call_duration')) {
                    $table->integer('voice_call_duration')->default(0)->nullable();
                }
            });
        }

        // Create optional call logs table for detailed analytics
        if (!Schema::hasTable('ext_chatbot_call_logs')) {
            Schema::create('ext_chatbot_call_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('chatbot_id')->index();
                $table->unsignedBigInteger('conversation_id')->nullable();
                $table->string('call_sid')->unique();
                $table->string('from_number');
                $table->string('to_number');
                $table->enum('call_status', ['queued', 'ringing', 'in-progress', 'completed', 'failed', 'no-answer', 'canceled', 'transferred'])->default('queued');
                $table->integer('call_duration_seconds')->default(0);
                $table->unsignedBigInteger('transfer_to_agent_id')->nullable();
                $table->string('recording_url')->nullable();
                $table->text('recording_notes')->nullable();
                $table->decimal('cost', 8, 4)->nullable();
                $table->string('cost_currency', 3)->default('USD');
                $table->json('metadata')->nullable(); // Store any extra Twilio data
                $table->timestamps();

                $table->foreign('chatbot_id')->references('id')->on('ext_chatbots')->onDelete('cascade');
                $table->foreign('conversation_id')->references('id')->on('ext_chatbot_conversations')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ext_chatbot_call_logs')) {
            Schema::dropIfExists('ext_chatbot_call_logs');
        }

        if (Schema::hasTable('ext_chatbot_conversations')) {
            Schema::table('ext_chatbot_conversations', function (Blueprint $table) {
                $table->dropColumnIfExists([
                    'call_phone_number',
                    'call_status',
                    'call_started_at',
                    'call_ended_at',
                    'call_duration_seconds',
                ]);
            });
        }

        if (Schema::hasTable('ext_chatbot_histories')) {
            Schema::table('ext_chatbot_histories', function (Blueprint $table) {
                $table->dropColumnIfExists('voice_call_duration');
            });
        }

        if (Schema::hasTable('ext_chatbot_channels')) {
            Schema::table('ext_chatbot_channels', function (Blueprint $table) {
                $table->dropColumnIfExists('channel_type');
            });
        }
    }
};
