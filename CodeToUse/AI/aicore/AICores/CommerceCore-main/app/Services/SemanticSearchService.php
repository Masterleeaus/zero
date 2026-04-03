<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Support\Collection;

class SemanticSearchService
{
    /**
     * Perform a semantic search for products based on a natural language query.
     */
    public function search(Store $store, string $query): Collection
    {
        $query = strtolower($query);
        
        // 1. Exact Match Check (Highest Priority)
        $exactMatches = Product::where('store_id', $store->id)
            ->where('status', 'active')
            ->where('name', 'LIKE', "%{$query}%")
            ->get();

        if ($exactMatches->count() >= 3) {
            return $exactMatches->take(10);
        }

        // 2. Intent Extraction (Very Basic NLP rules)
        $intent = $this->parseIntent($query);
        
        // 3. Category matching
        $categoryIds = [];
        if ($intent['category']) {
            $categoryIds = Category::where('store_id', $store->id)
                ->where('name', 'LIKE', "%{$intent['category']}%")
                ->pluck('id')
                ->toArray();
        }

        // 4. Build Hybrid Query
        $productsQuery = Product::where('store_id', $store->id)
            ->where('status', 'active');

        $productsQuery->where(function($q) use ($intent, $categoryIds, $query) {
            // Match categories found
            if (!empty($categoryIds)) {
                $q->orWhereIn('category_id', $categoryIds);
            }

            // Match extracted keywords
            foreach ($intent['keywords'] as $keyword) {
                if (strlen($keyword) > 2) {
                    $q->orWhere('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('description', 'LIKE', "%{$keyword}%");
                }
            }

            // Price range filter (if mentioned)
            if ($intent['max_price']) {
                $q->where('price', '<=', $intent['max_price']);
            }
        });

        // 5. Ranking (Relevance scoring simulation)
        $results = $productsQuery->get()->map(function($product) use ($query, $intent, $categoryIds) {
            $score = 0;
            $nameLower = strtolower($product->name);
            
            // Boost if query is in name
            if (str_contains($nameLower, $query)) $score += 10;
            
            // Boost category matches
            if (in_array($product->category_id, $categoryIds)) $score += 5;
            
            // Boost results within price intent
            if ($intent['max_price'] && $product->price <= $intent['max_price']) $score += 3;

            $product->search_score = $score;
            return $product;
        })->sortByDesc('search_score');

        return $results->values()->take(10);
    }

    /**
     * Simulated intent parser to extract categories, price points, and keywords.
     */
    private function parseIntent(string $query): array
    {
        $intent = [
            'category' => null,
            'max_price' => null,
            'keywords' => [],
            'sort' => 'relevance'
        ];

        // Price detection (e.g., "under 50", "below 100")
        if (preg_match('/(?:under|below|less than)\s*(?:\$|usd)?\s*(\d+)/i', $query, $matches)) {
            $intent['max_price'] = (float)$matches[1];
        }

        // Clean query of common stop words to get keywords
        $stopWords = ['find', 'me', 'show', 'all', 'the', 'under', 'below', 'with', 'a', 'search', 'for'];
        $words = explode(' ', $query);
        $intent['keywords'] = array_filter($words, fn($w) => !in_array($w, $stopWords) && strlen($w) > 2);

        // Simple category guessing (first non-stop word often acts as category in short queries)
        if (!empty($intent['keywords'])) {
            $intent['category'] = reset($intent['keywords']);
        }

        return $intent;
    }
}
