<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ContactSubmission;
use App\Models\Store;

class ContactController extends Controller
{
    public function submit(Request $request, string $storeSlug)
    {
        $store = Store::where('slug', $storeSlug)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        ContactSubmission::create(array_merge($validated, [
            'store_id' => $store->id,
            'status' => 'unread'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent successfully!'
        ]);
    }
}
