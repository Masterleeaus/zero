<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Exception for channel-specific errors (Telegram, Messenger, WhatsApp, etc.).
 * Includes webhook retry metadata and driver context.
 */
class OmniChannelException extends OmniException
{
    protected string $driver;
    protected string $webhookId;
    protected bool $retriable;

    public function __construct(
        string $driver,
        string $message = '',
        int $statusCode = 400,
        string $errorCode = 'CHANNEL_ERROR',
        string $webhookId = '',
        bool $retriable = true,
        array $context = []
    ) {
        $this->driver = $driver;
        $this->webhookId = $webhookId;
        $this->retriable = $retriable;

        $context['driver'] = $driver;
        $context['webhook_id'] = $webhookId;
        $context['retriable'] = $retriable;

        parent::__construct($message, 0, null, $statusCode, $errorCode, $context);
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getWebhookId(): string
    {
        return $this->webhookId;
    }

    public function isRetriable(): bool
    {
        return $this->retriable;
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->message,
                'driver' => $this->driver,
                'webhook_id' => $this->webhookId,
                'retriable' => $this->retriable,
                'status' => $this->statusCode,
                'timestamp' => now()->toIso8601String(),
            ],
        ], $this->statusCode);
    }

    public function report(): void
    {
        \Log::error("Omni Channel Exception [{$this->driver}]", [
            'message' => $this->message,
            'webhook_id' => $this->webhookId,
            'retriable' => $this->retriable,
            'context' => $this->context,
            'trace' => $this->getTraceAsString(),
        ]);
    }
}
