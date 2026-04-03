<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStore
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('store');

        if ($slug) {
            $store = Store::where('slug', $slug)->where('status', 'active')->first();

            if (!$store) {
                abort(404, 'Store not found');
            }

            $request->merge(['current_store' => $store]);
            view()->share('currentStore', $store);
        }

        return $next($request);
    }
}
