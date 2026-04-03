<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;

class VoiceSearchController extends Controller
{
    public function search(Request $request, string $storeSlug, \App\Services\SemanticSearchService $searchService)
    {
        $store = Store::where('slug', $storeSlug)->firstOrFail();
        $transcript = $request->input('transcript', '');

        if (empty($transcript)) {
            return response()->json(['products' => [], 'message' => 'No audio detected.']);
        }

        // Use Semantic search for hybrid results
        $products = $searchService->search($store, $transcript);

        return response()->json([
            'products' => $products,
            'transcript' => $transcript,
            'match_count' => $products->count()
        ]);
    }
}
