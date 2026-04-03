<?php

namespace Modules\Workflow\Conditions\Payload;

class PayloadAccessor
{
    /**
     * Get a nested payload value by dot path: "attributes.status"
     */
    public static function get(array $payload, string $path, mixed $default = null): mixed
    {
        $segments = $path === '' ? [] : explode('.', $path);
        $cur = $payload;
        foreach ($segments as $seg) {
            if (!is_array($cur) || !array_key_exists($seg, $cur)) {
                return $default;
            }
            $cur = $cur[$seg];
        }
        return $cur;
    }
}
