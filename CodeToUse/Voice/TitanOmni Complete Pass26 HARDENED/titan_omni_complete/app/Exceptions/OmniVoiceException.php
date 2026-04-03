<?php

namespace App\Exceptions;

/**
 * Exception for voice operations (PSTN calls, transcription, TTS, callbacks).
 * Includes call metadata and service availability context.
 */
class OmniVoiceException extends OmniException
{
    public static function callInitiationFailed(string $toNumber, ?string $reason = null): self
    {
        return new self(
            "Failed to initiate call to {$toNumber}",
            0,
            null,
            503,
            'VOICE_CALL_INIT_FAILED',
            ['to_number' => $toNumber, 'reason' => $reason]
        );
    }

    public static function transcriptionFailed(string $callSid, ?string $reason = null): self
    {
        return new self(
            "Transcription failed for call {$callSid}",
            0,
            null,
            503,
            'VOICE_TRANSCRIPTION_FAILED',
            ['call_sid' => $callSid, 'reason' => $reason]
        );
    }

    public static function ttsGenerationFailed(string $text, string $voiceModel = 'default', ?string $reason = null): self
    {
        return new self(
            "TTS generation failed for voice model {$voiceModel}",
            0,
            null,
            503,
            'VOICE_TTS_FAILED',
            ['text_length' => strlen($text), 'voice_model' => $voiceModel, 'reason' => $reason]
        );
    }

    public static function recordingNotAvailable(string $callSid, ?string $reason = null): self
    {
        return new self(
            "Recording unavailable for call {$callSid}",
            0,
            null,
            404,
            'VOICE_RECORDING_NOT_FOUND',
            ['call_sid' => $callSid, 'reason' => $reason]
        );
    }

    public static function invalidPhoneNumber(string $phoneNumber): self
    {
        return new self(
            "Invalid phone number format: {$phoneNumber}",
            0,
            null,
            400,
            'VOICE_INVALID_PHONE',
            ['phone' => $phoneNumber]
        );
    }
}
