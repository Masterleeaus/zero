<?php

namespace App\Titan\Signals;

class SignalRegistry
{
    public function definitions(): array
    {
        return (array) config('titan_signal.registry', []);
    }

    public function definitionFor(string $type): array
    {
        return $this->definitions()[$type] ?? [];
    }

    public function requiredPayloadFields(string $type): array
    {
        return (array) data_get($this->definitionFor($type), 'required_payload_fields', []);
    }

    public function allowedSeverities(string $type): array
    {
        return (array) data_get($this->definitionFor($type), 'allowed_severities', SignalSeverity::all());
    }

    public function defaultKind(string $type, ?string $fallback = null): string
    {
        return (string) (data_get($this->definitionFor($type), 'kind') ?: $fallback ?: 'generic');
    }

    public function domain(string $type, ?string $fallback = null): ?string
    {
        $domain = data_get($this->definitionFor($type), 'domain');

        return $domain !== null ? (string) $domain : $fallback;
    }

    public function approvalRules(string $type): array
    {
        return (array) data_get($this->definitionFor($type), 'approval', []);
    }
}
