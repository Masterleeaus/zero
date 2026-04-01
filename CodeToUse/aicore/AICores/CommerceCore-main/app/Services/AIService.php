<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Define the endpoint (Simulation or real OPENAI)
     */
    protected $endpoint = 'https://api.openai.com/v1/chat/completions';
    
    /**
     * API Key loaded from env
     */
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', 'mock_key');
    }

    /**
     * Generates a descriptive smart paragraph using AI and given inputs
     */
    public function generateProductDescription($productName, $category, $tags = '')
    {
        $prompt = "Write a compelling, SEO-optimized product description for an e-commerce platform. "
                . "The product is called '{$productName}' and belongs to the '{$category}' category. "
                . "Key attributes or tags: {$tags}. Make it professional, persuasive, and under 150 words.";

        // For local development / demonstration without a real LLM API key
        if ($this->apiKey === 'mock_key' || env('APP_ENV') === 'testing' || empty($this->apiKey)) {
            return $this->mockResponse($productName, $category);
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->post($this->endpoint, [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an expert copywriter for a top-tier SaaS E-commerce site.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => 200,
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('AI Generation Failed: ' . $response->body());
            return "Failed to generate description. Please try again later.";

        } catch (\Exception $e) {
            Log::error('AI Generation Request Error: ' . $e->getMessage());
            return "Failed to generate description due to a network error.";
        }
    }

    /**
     * Hardcoded smart description generator for simulation purposes 
     * where real OpenAI/Anthropic keys are missing.
     */
    protected function mockResponse($productName, $category)
    {
        $adjectives = ['innovative', 'premium', 'high-quality', 'essential', 'cutting-edge'];
        $adj = $adjectives[array_rand($adjectives)];
        
        return "Introducing the {$adj} {$productName}, a standout addition to our {$category} collection. " .
               "Designed with meticulous attention to detail, this product is tailored to elevate your everyday experience. " .
               "Whether you're looking for performance, durability, or style, the {$productName} delivers exceptional value. " .
               "Don't miss the chance to upgrade your lifestyle with this must-have item. Order yours today and experience " .
               "unparalleled satisfaction and quality.";
    }
}
