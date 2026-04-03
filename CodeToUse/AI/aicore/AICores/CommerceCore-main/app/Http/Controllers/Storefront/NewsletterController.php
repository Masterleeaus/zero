<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\NewsletterSubscriber;
use App\Models\Store;

class NewsletterController extends Controller
{
    public function subscribe(Request $request, string $storeSlug)
    {
        $store = Store::where('slug', $storeSlug)->firstOrFail();

        $validated = $request->validate([
            'email' => 'required|email',
            'first_name' => 'nullable|string|max:100',
        ]);

        NewsletterSubscriber::updateOrCreate(
            ['store_id' => $store->id, 'email' => $validated['email']],
            ['first_name' => $validated['first_name'], 'status' => 'active']
        );

        return response()->json([
            'success' => true,
            'message' => 'Thank you for subscribing!'
        ]);
    }
}
