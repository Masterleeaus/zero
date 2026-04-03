<?php

namespace Tests\Performance;

use App\Models\Store;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StorefrontPerformanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Measure the performance of the storefront homepage with and without caching.
     */
    public function test_homepage_performance()
    {
        // 1. Setup a demo store with some content
        $store = Store::factory()->create(['name' => 'Perf Test Store']);
        
        $url = route('storefront.home', ['store' => $store->slug]);
        
        // Ensure cache is cleared initially
        Cache::forget("storefront:{$store->id}:homepage");
        
        // 2. Cold Hit (No Cache)
        $startCold = microtime(true);
        $this->get($url);
        $endCold = microtime(true);
        $coldTime = ($endCold - $startCold) * 1000;
        
        // 3. Warm Hits (With Cache)
        $warmTimes = [];
        for ($i = 0; $i < 10; $i++) {
            $startWarm = microtime(true);
            $this->get($url);
            $endWarm = microtime(true);
            $warmTimes[] = ($endWarm - $startWarm) * 1000;
        }
        
        $avgWarmTime = array_sum($warmTimes) / count($warmTimes);
        
        echo "\n--- Performance Results for Store: {$store->name} ---\n";
        echo "Cold Hit (No Cache): " . round($coldTime, 2) . "ms\n";
        echo "Warm Hit (Cached):   " . round($avgWarmTime, 2) . "ms\n";
        echo "Performance Gain:    " . round((($coldTime - $avgWarmTime) / $coldTime) * 100, 2) . "%\n";
        echo "-----------------------------------------------\n";
        
        $this->assertTrue($avgWarmTime < $coldTime, "Cached response should be faster than cold response");
    }
}
