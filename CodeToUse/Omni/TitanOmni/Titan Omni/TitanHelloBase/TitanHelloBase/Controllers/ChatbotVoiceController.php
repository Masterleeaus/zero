<?php

namespace Extensions\TitanHello\Controllers;

use App\Domains\Entity\Enums\EntityEnum;
use App\Domains\Entity\Facades\Entity as EntityFacade;
use Extensions\TitanHello\Helpers\Requests\VoiceChatbotStoreRequest;
use Extensions\TitanHello\Helpers\Requests\VoiceChatbotUpdateRequest;
use Extensions\TitanHello\Models\ExtVoiceChatbot;
use Extensions\TitanHello\Models\TitanHelloCallSession;
use Extensions\TitanHello\Models\TitanHelloLead;
use Extensions\TitanHello\Services\ChabotVoiceService;
use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use App\Services\Ai\ElevenLabsService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class ChatbotVoiceController extends Controller
{
    public function __construct(public ChabotVoiceService $service) {}

    // index
    public function index(): View
    {
        $chatbots = $this->service->query()
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')->paginate(perPage: 10);

        // Phone answering dashboard (last 24h)
        $since = now()->subDay();
        $recentCalls = TitanHelloCallSession::query()
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $missedCalls = TitanHelloCallSession::query()
            ->where('created_at', '>=', $since)
            ->whereIn('status', ['no-answer', 'busy', 'failed', 'canceled'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $hotLeads = TitanHelloLead::query()
            ->where('created_at', '>=', $since)
            ->whereIn('urgency', ['high', 'urgent'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('titan-hello::admin.index', [
            'chatbots' => $chatbots,
            'avatars' 	=> $this->service->avatars(),
            'voices' 	 => $this->service->getVoices(),
            'recentCalls' => $recentCalls,
            'missedCalls' => $missedCalls,
            'hotLeads' => $hotLeads,
        ]);
    }

    // store voice chatbot
    public function store(VoiceChatbotStoreRequest $request): JsonResource|JsonResponse
    {
        if (Helper::appIsDemo()) {
            return response()->json([
                'type'    => 'error',
                'message' => 'This feature is disabled in Demo version.',
            ], 403);
        }

        $reqData = $request->validated();
        if (isset($reqData['language']) && $reqData['language'] == 'auto') {
            unset($reqData['language']);
        }

        $res = $this->service->createAgent($reqData);

        if ($res->getData()->status === 'success') {
            $reqData['agent_id'] = $res->getData()->resData->agent_id;
            $reqData['voice_id'] = ElevenLabsService::DEFAULT_ELEVENLABS_VOICE_ID;
            $reqData['ai_model'] = ElevenLabsService::DEFAULT_ELEVENLABS_MODEL;
            if (isset($reqData['language']) && $reqData['language'] == 'en') {
                $reqData['ai_model'] = ElevenLabsService::DEFAULT_ELEVENLABS_MODEL_FOR_ENGLISH;
            }
            $chatbot = ExtVoiceChatbot::create($reqData);

            return JsonResource::make($chatbot);
        }

        return $res->setStatusCode(422);

    }

    // update voice chatbot
    public function update(VoiceChatbotUpdateRequest $request): JsonResource|JsonResponse
    {
        if (Helper::appIsDemo()) {
            return response()->json([
                'type'    => 'error',
                'message' => 'This feature is disabled in Demo version.',
            ], 403);
        }

        $reqData = $request->validated();

        if (isset($reqData['language']) && $reqData['language'] == 'auto') {
            unset($reqData['language']);
        }

        $chatbot = ExtVoiceChatbot::findOrFail($reqData['id']);
        $chatbot?->update($reqData);

        $this->service->updateAgent($chatbot->id);

        return JsonResource::make($chatbot);
    }

    // delete voice chatbot
    public function delete(Request $request): JsonResponse
    {
        if (Helper::appIsDemo()) {
            return response()->json([
                'type'    => 'error',
                'message' => 'This feature is disabled in Demo version.',
            ], 403);
        }

        $request->validate(['id' => 'required']);

        $chatbot = $this->service->query()->findOrFail($request->get('id'));

        if ($chatbot->getAttribute('user_id') === Auth::id()) {
            $this->service->deleteAgent($chatbot->agent_id);
            $chatbot->delete();
        } else {
            abort(403);
        }

        return response()->json([
            'message' => 'Voice Chatbot deleted successfully',
            'type'    => 'success',
            'status'  => 200,
        ]);
    }

    /**
     * voice chatbot frame view
     */
    public function frame(string $uuid): View|Response
    {
        $chatbot = ExtVoiceChatbot::whereUuid($uuid)->firstOrFail();
        if ($chatbot) {
            return view('titan-hello::admin.frame', compact('chatbot'));
        } else {
            return response('Incorrect UUID', 404);
        }
    }

    public function checkVoiceBalance(Request $request): ?JsonResponse
    {
        if (Helper::appIsDemo()) {
            $onStart = $request->input('onStart', false);
            $key = ($onStart ? 'onstart-voice-chat-attempt-:' : 'voice-chat-attempt-:') . (request()?->header('cf-connecting-ip') ?? request()?->ip());
            $tryCount = $onStart ? 1 : 4;
            if (! RateLimiter::tooManyAttempts($key, $tryCount)) {
                RateLimiter::hit($key, 60 * 60 * 24);

                return response()->json(['status' => 'success', 'message' => 'Demo mode'], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'Exceeded messages limit on demo'], 200);
        }
        $uuId = $request->input('uuId');
        $chatbot = ExtVoiceChatbot::whereUuid($uuId)->first();
        if (! empty($chatbot)) {
            $user = $chatbot->user;
            $driver = EntityFacade::driver(EntityEnum::ELEVENLABS_VOICE_CHATBOT)->forUser($user);

            try {
                $driver->redirectIfNoCreditBalance();
            } catch (Exception $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'status'  => 'error',
                ], 200);
            }
        }

        return response()->json(['status' => 'success', 'message' => ''], 200);
    }
}
