<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AIService;

class AIController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Endpoint to generate product descriptions via AI
     */
    public function generateDescription(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
        ]);

        $description = $this->aiService->generateProductDescription(
            $validatedData['name'], 
            $validatedData['category'] ?? 'General',
            $validatedData['tags'] ?? ''
        );

        return response()->json([
            'message' => 'Description generated successfully.',
            'description' => $description
        ]);
    }
}
