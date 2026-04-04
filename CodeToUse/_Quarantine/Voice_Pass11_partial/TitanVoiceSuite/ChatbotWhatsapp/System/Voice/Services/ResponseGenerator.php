<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System\Voice\Services;

class ResponseGenerator
{
    public function success(string $intent, array $result): string
    {
        return (string) ($result['message'] ?? sprintf('Done: %s.', str_replace('_', ' ', $intent)));
    }

    public function confirm(string $intent, array $entities = []): string
    {
        $context = $entities === [] ? '' : ' with ' . implode(', ', array_map(
            static fn ($key, $value): string => sprintf('%s %s', str_replace('_', ' ', (string) $key), (string) $value),
            array_keys($entities),
            $entities
        ));

        return sprintf('Please confirm %s%s.', str_replace('_', ' ', $intent), $context);
    }

    public function permissionDenied(string $intent): string
    {
        return sprintf("I don't have permission to %s.", str_replace('_', ' ', $intent));
    }

    public function missingInformation(string $intent, array $missing): string
    {
        return sprintf(
            'I can help with %s, but I still need: %s.',
            str_replace('_', ' ', $intent),
            implode(', ', array_map(static fn (string $field): string => str_replace('_', ' ', $field), $missing))
        );
    }
}
