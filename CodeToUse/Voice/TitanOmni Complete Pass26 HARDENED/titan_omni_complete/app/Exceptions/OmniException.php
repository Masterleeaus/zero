<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base exception for TitanOmni system errors.
 * Provides structured error responses with logging context.
 */
class OmniException extends Exception
{
    protected int $statusCode = 500;
    protected string $errorCode = 'OMNI_ERROR';
    protected array $context = [];

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        int $statusCode = 500,
        string $errorCode = 'OMNI_ERROR',
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->message,
                'status' => $this->statusCode,
                'timestamp' => now()->toIso8601String(),
                'context' => $this->context,
            ],
        ], $this->statusCode);
    }

    public function report(): void
    {
        \Log::error("Omni Exception: {$this->errorCode}", [
            'message' => $this->message,
            'code' => $this->code,
            'status' => $this->statusCode,
            'context' => $this->context,
            'trace' => $this->getTraceAsString(),
        ]);
    }
}
