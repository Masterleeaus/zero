<?php

namespace App\TitanCore\Zero\Memory\Session;

class SessionHandoffManager
{
    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    public function export(string $sessionKey, array $snapshot = []): array
    {
        return [
            'session_key' => $sessionKey,
            'handoff_ready' => true,
            'snapshot' => $snapshot,
            'exported_at' => now()->toIso8601String(),
        ];
    }
}
