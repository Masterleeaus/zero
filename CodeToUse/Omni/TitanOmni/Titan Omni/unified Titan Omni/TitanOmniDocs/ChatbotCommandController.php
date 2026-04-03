<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotChannel;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use App\Extensions\Chatbot\System\Services\ChatbotService;
use App\Extensions\Chatbot\System\Services\ChatbotAnalyticsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatbotRequest;
use App\Http\Requests\UpdateChatbotRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\Paginator;

class ChatbotCommandController extends Controller
{
    protected ChatbotService $chatbotService;
    protected ChatbotAnalyticsService $analyticsService;

    public function __construct(
        ChatbotService $chatbotService,
        ChatbotAnalyticsService $analyticsService
    ) {
        $this->chatbotService = $chatbotService;
        $this->analyticsService = $analyticsService;
        
        // Authorize: only users with manage-chatbots permission
        $this->authorizeResource(Chatbot::class, 'chatbot');
    }

    /**
     * Display a listing of chatbots for this workspace
     */
    public function index(): View
    {
        $workspace = auth()->user()->workspace;

        $chatbots = Chatbot::where('workspace_id', $workspace->id)
            ->with([
                'channels',
                'conversations' => function ($query) {
                    $query->where('closed', false);
                },
            ])
            ->withCount([
                'conversations',
                'conversations as open_conversations' => function ($query) {
                    $query->where('closed', false);
                },
            ])
            ->latest()
            ->paginate(15);

        // Quick stats
        $stats = [
            'total_chatbots' => Chatbot::where('workspace_id', $workspace->id)->count(),
            'active_conversations' => ChatbotConversation::whereHas('chatbot', function ($query) use ($workspace) {
                $query->where('workspace_id', $workspace->id);
            })->where('closed', false)->count(),
            'total_conversations' => ChatbotConversation::whereHas('chatbot', function ($query) use ($workspace) {
                $query->where('workspace_id', $workspace->id);
            })->count(),
        ];

        return view('dashboard.chatbots.index', [
            'chatbots' => $chatbots,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new chatbot
     */
    public function create(): View
    {
        return view('dashboard.chatbots.create', [
            'availableModels' => [
                'claude-opus' => 'Claude Opus (Most intelligent)',
                'claude-sonnet' => 'Claude Sonnet (Balanced)',
                'claude-haiku' => 'Claude Haiku (Fast)',
                'gpt-4' => 'GPT-4 (OpenAI)',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo (OpenAI)',
            ],
            'availableChannels' => [
                'telegram' => 'Telegram',
                'whatsapp' => 'WhatsApp',
                'messenger' => 'Facebook Messenger',
                'voice' => 'Voice (Phone)',
                'external' => 'Website Embed',
            ],
        ]);
    }

    /**
     * Store a newly created chatbot
     */
    public function store(StoreChatbotRequest $request): RedirectResponse
    {
        $workspace = auth()->user()->workspace;

        $chatbot = \DB::transaction(function () use ($request, $workspace) {
            // Create chatbot
            $chatbot = Chatbot::create([
                'workspace_id' => $workspace->id,
                'user_id' => auth()->id(),
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'ai_model' => $request->input('ai_model', 'claude-sonnet'),
                'interaction_type' => $request->input('interaction_type', 'SMART_SWITCH'),
                'welcome_message' => $request->input('welcome_message', 'Hi! How can I help you?'),
                'bubble_message' => $request->input('bubble_message', 'Hey there. How can I help you?'),
                'avatar' => $request->input('avatar'),
                'logo' => $request->input('logo'),
            ]);

            // Create channels
            $channels = $request->input('channels', ['telegram', 'whatsapp']);
            foreach ($channels as $channelType) {
                ChatbotChannel::create([
                    'chatbot_id' => $chatbot->id,
                    'channel_type' => $channelType,
                    'channel_config' => json_encode([
                        'is_active' => true,
                        'webhook_url' => route('api.chatbot.webhook', [
                            'chatbot' => $chatbot->id,
                            'channel' => $channelType,
                        ]),
                    ]),
                ]);
            }

            return $chatbot;
        });

        return redirect()->route('dashboard.chatbots.show', $chatbot)
            ->with('success', 'Chatbot created successfully!');
    }

    /**
     * Display the specified chatbot with detailed view
     */
    public function show(Chatbot $chatbot): View
    {
        $workspace = auth()->user()->workspace;
        
        // Verify workspace ownership
        if ($chatbot->workspace_id !== $workspace->id) {
            abort(403);
        }

        $conversations = ChatbotConversation::where('chatbot_id', $chatbot->id)
            ->with(['customer', 'assignedAgent', 'histories' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->where('closed', false)
            ->latest('last_activity_at')
            ->paginate(20);

        $analytics = $this->analyticsService->getMetrics($chatbot);

        return view('dashboard.chatbots.show', [
            'chatbot' => $chatbot->load('channels', 'knowledgeBases'),
            'conversations' => $conversations,
            'analytics' => $analytics,
            'openConversationsCount' => ChatbotConversation::where('chatbot_id', $chatbot->id)
                ->where('closed', false)
                ->count(),
        ]);
    }

    /**
     * Show the form for editing the chatbot
     */
    public function edit(Chatbot $chatbot): View
    {
        $workspace = auth()->user()->workspace;
        
        if ($chatbot->workspace_id !== $workspace->id) {
            abort(403);
        }

        return view('dashboard.chatbots.edit', [
            'chatbot' => $chatbot->load('channels'),
            'availableModels' => [
                'claude-opus' => 'Claude Opus (Most intelligent)',
                'claude-sonnet' => 'Claude Sonnet (Balanced)',
                'claude-haiku' => 'Claude Haiku (Fast)',
                'gpt-4' => 'GPT-4 (OpenAI)',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo (OpenAI)',
            ],
            'availableChannels' => [
                'telegram' => 'Telegram',
                'whatsapp' => 'WhatsApp',
                'messenger' => 'Facebook Messenger',
                'voice' => 'Voice (Phone)',
                'external' => 'Website Embed',
            ],
        ]);
    }

    /**
     * Update the specified chatbot
     */
    public function update(UpdateChatbotRequest $request, Chatbot $chatbot): RedirectResponse
    {
        $workspace = auth()->user()->workspace;
        
        if ($chatbot->workspace_id !== $workspace->id) {
            abort(403);
        }

        $chatbot->update($request->validated());

        // Update channels if provided
        if ($request->has('channels')) {
            $newChannels = $request->input('channels', []);
            $existingChannels = $chatbot->channels()->pluck('channel_type')->toArray();

            // Remove deleted channels
            $channelsToDelete = array_diff($existingChannels, $newChannels);
            if (count($channelsToDelete) > 0) {
                ChatbotChannel::where('chatbot_id', $chatbot->id)
                    ->whereIn('channel_type', $channelsToDelete)
                    ->delete();
            }

            // Add new channels
            $channelsToAdd = array_diff($newChannels, $existingChannels);
            foreach ($channelsToAdd as $channelType) {
                ChatbotChannel::create([
                    'chatbot_id' => $chatbot->id,
                    'channel_type' => $channelType,
                    'channel_config' => json_encode([
                        'is_active' => true,
                        'webhook_url' => route('api.chatbot.webhook', [
                            'chatbot' => $chatbot->id,
                            'channel' => $channelType,
                        ]),
                    ]),
                ]);
            }
        }

        return redirect()->route('dashboard.chatbots.show', $chatbot)
            ->with('success', 'Chatbot updated successfully!');
    }

    /**
     * Delete the specified chatbot
     */
    public function destroy(Chatbot $chatbot): RedirectResponse
    {
        $workspace = auth()->user()->workspace;
        
        if ($chatbot->workspace_id !== $workspace->id) {
            abort(403);
        }

        $name = $chatbot->name;
        $chatbot->delete();

        return redirect()->route('dashboard.chatbots.index')
            ->with('success', "Chatbot '{$name}' deleted successfully.");
    }

    /**
     * Get analytics for a specific chatbot (API endpoint)
     */
    public function analytics(Chatbot $chatbot): JsonResponse
    {
        $workspace = auth()->user()->workspace;
        
        if ($chatbot->workspace_id !== $workspace->id) {
            abort(403);
        }

        $metrics = $this->analyticsService->getMetrics($chatbot);
        $byChannel = $this->analyticsService->getChannelStats($chatbot);
        $byDay = $this->analyticsService->getConversationsByDay($chatbot, 30);

        return response()->json([
            'metrics' => $metrics,
            'by_channel' => $byChannel,
            'by_day' => $byDay,
        ]);
    }

    /**
     * Get conversations for this chatbot (API endpoint)
     */
    public function conversations(Chatbot $chatbot, \Illuminate\Http\Request $request): JsonResponse
    {
        $workspace = auth()->user()->workspace;
        
        if ($chatbot->workspace_id !== $workspace->id) {
            abort(403);
        }

        $filter = $request->input('filter', 'open'); // open, closed, all
        $query = ChatbotConversation::where('chatbot_id', $chatbot->id);

        if ($filter === 'open') {
            $query->where('closed', false);
        } elseif ($filter === 'closed') {
            $query->where('closed', true);
        }

        $conversations = $query
            ->with(['customer', 'assignedAgent'])
            ->latest('last_activity_at')
            ->paginate($request->input('per_page', 50));

        return response()->json($conversations);
    }

    /**
     * Assign an agent to a chatbot queue
     */
    public function assignAgent(Chatbot $chatbot, \Illuminate\Http\Request $request): RedirectResponse
    {
        $workspace = auth()->user()->workspace;
        
        if ($chatbot->workspace_id !== $workspace->id) {
            abort(403);
        }

        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
        ]);

        $agent = $workspace->users()->findOrFail($validated['agent_id']);

        // Store agent assignment (you might want a separate table for this)
        // For now, we'll assume conversations are assigned on-demand
        // But you could create a ChatbotAgent model if needed

        return back()->with('success', "{$agent->name} assigned as agent for this chatbot.");
    }

    /**
     * Get webhook URL for a chatbot channel
     */
    public function webhookUrl(Chatbot $chatbot, string $channel): JsonResponse
    {
        $workspace = auth()->user()->workspace;
        
        if ($chatbot->workspace_id !== $workspace->id) {
            abort(403);
        }

        $channelConfig = ChatbotChannel::where('chatbot_id', $chatbot->id)
            ->where('channel_type', $channel)
            ->first();

        if (!$channelConfig) {
            return response()->json(['error' => 'Channel not found'], 404);
        }

        return response()->json([
            'channel' => $channel,
            'webhook_url' => route('api.chatbot.webhook', [
                'chatbot' => $chatbot->id,
                'channel' => $channel,
            ]),
            'instructions' => $this->getWebhookInstructions($channel),
        ]);
    }

    /**
     * Get setup instructions for a specific channel
     */
    private function getWebhookInstructions(string $channel): string
    {
        return match($channel) {
            'telegram' => 'Go to BotFather on Telegram, set webhook URL to: ',
            'whatsapp' => 'In Twilio console, set webhook URL to: ',
            'messenger' => 'In Facebook App settings, set webhook URL to: ',
            'voice' => 'In Twilio phone settings, set webhook URL to: ',
            default => 'Set webhook URL to: ',
        };
    }
}
