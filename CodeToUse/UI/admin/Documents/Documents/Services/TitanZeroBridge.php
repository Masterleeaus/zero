<?php

namespace Modules\Documents\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class TitanZeroBridge
{
    /**
     * Build the canonical Titan Zero context payload for the current page.
     *
     * NOTE: This module never calls providers directly. It only packages context.
     */
    public static function makePayload(Request $request, ?string $intent, ?array $record = null, array $fields = []): array
    {
        return [
            'intent' => $intent,
            'return_url' => url()->current(),
            'page' => [
                'route_name' => optional($request->route())->getName(),
                'url' => url()->current(),
            ],
            'record' => $record ?? [
                'record_type' => 'document',
                'record_id' => null,
            ],
            'fields' => $fields,
            'user_id' => auth()->id(),
            'company_id' => function_exists('company') && company() ? company()->id : (auth()->user()->company_id ?? null),
        ];
    }

    public static function hasIntentRoute(): bool
    {
        return Route::has('titan.zero.intent.run') || Route::has('titan.zero.heroes.ask');
    }
}
