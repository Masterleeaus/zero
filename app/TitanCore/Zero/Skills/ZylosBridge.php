<?php

namespace App\TitanCore\Zero\Skills;

/**
 * ZylosBridge — async skill dispatch gateway.
 *
 * Routes skill execution requests to the Zylos skill runtime via HTTP.
 * No direct database writes are performed; all state changes flow back
 * via the signed callback endpoint.
 */
class ZylosBridge
{
    public function __construct(
        protected \Illuminate\Http\Client\Factory $http,
    ) {
    }

    /**
     * Dispatch a skill execution request to the Zylos runtime.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function dispatch(string $skillSlug, array $payload): array
    {
        $endpoint = rtrim((string) config('titan_core.zylos.endpoint', ''), '/');
        $secret   = (string) config('titan_core.zylos.secret', '');

        if ($endpoint === '') {
            return [
                'ok'      => false,
                'status'  => 'misconfigured',
                'message' => 'ZYLOS_ENDPOINT is not configured.',
                'skill'   => $skillSlug,
            ];
        }

        $body = array_merge($payload, [
            'skill'         => $skillSlug,
            'dispatched_at' => now()->toIso8601String(),
        ]);

        $signature = hash_hmac('sha256', json_encode($body, JSON_UNESCAPED_UNICODE), $secret);

        try {
            $response = $this->http
                ->withHeaders([
                    'X-Zylos-Signature' => $signature,
                    'Content-Type'      => 'application/json',
                    'Accept'            => 'application/json',
                ])
                ->timeout((int) config('titan_core.zylos.timeout', 10))
                ->post("{$endpoint}/dispatch", $body);

            return [
                'ok'            => $response->successful(),
                'status'        => $response->status(),
                'skill'         => $skillSlug,
                'response'      => $response->json() ?? [],
                'dispatched_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            return [
                'ok'      => false,
                'status'  => 'error',
                'skill'   => $skillSlug,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Query the status of a previously dispatched skill execution.
     *
     * @return array<string, mixed>
     */
    public function status(string $executionId): array
    {
        $endpoint = rtrim((string) config('titan_core.zylos.endpoint', ''), '/');
        $secret   = (string) config('titan_core.zylos.secret', '');

        if ($endpoint === '') {
            return ['ok' => false, 'status' => 'misconfigured'];
        }

        $body      = ['execution_id' => $executionId];
        $signature = hash_hmac('sha256', json_encode($body, JSON_UNESCAPED_UNICODE), $secret);

        try {
            $response = $this->http
                ->withHeaders(['X-Zylos-Signature' => $signature])
                ->timeout((int) config('titan_core.zylos.timeout', 10))
                ->post("{$endpoint}/status", $body);

            return [
                'ok'           => $response->successful(),
                'status'       => $response->status(),
                'execution_id' => $executionId,
                'response'     => $response->json() ?? [],
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Return list of available skills from the Zylos runtime.
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        $endpoint = rtrim((string) config('titan_core.zylos.endpoint', ''), '/');

        if ($endpoint === '') {
            return ['ok' => false, 'status' => 'misconfigured', 'skills' => []];
        }

        try {
            $response = $this->http->timeout((int) config('titan_core.zylos.timeout', 10))->get("{$endpoint}/skills");

            return [
                'ok'     => $response->successful(),
                'skills' => $response->json('skills') ?? [],
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'skills' => [], 'message' => $e->getMessage()];
        }
    }

    /**
     * Validate an inbound Zylos callback signature.
     */
    public function validateCallback(string $rawBody, string $incomingSignature): bool
    {
        $secret   = (string) config('titan_core.zylos.secret', '');
        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $incomingSignature);
    }
}
