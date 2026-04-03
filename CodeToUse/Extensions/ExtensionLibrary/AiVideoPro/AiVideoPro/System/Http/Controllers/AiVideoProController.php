<?php

namespace App\Extensions\AiVideoPro\System\Http\Controllers;

use App\Domains\Engine\Services\FalAIService;
use App\Domains\Entity\Enums\EntityEnum;
use App\Domains\Entity\Facades\Entity;
use App\Extensions\AiVideoPro\System\Models\UserFall;
use App\Extensions\AiVideoPro\System\Services\SoraService;
use App\Helpers\Classes\ApiHelper;
use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use App\Packages\FalAI\FalAIService as PackageFalAIService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiVideoProController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $list = UserFall::query()->where('user_id', auth()->user()->id)->get()->toArray();

        $inProgress = collect($list)->filter(function ($entry) {
            return in_array($entry['status'], ['IN_QUEUE', 'queued']);
        })->pluck('id')->toArray();

        return view('ai-video-pro::index', compact(['list', 'inProgress']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return back()->with(['message' => 'This feature is disabled in demo mode.', 'type' => 'error']);
        }
        if (! ApiHelper::setFalAIKey()) {
            return back()->with(['message' => 'Please set FAL AI key.', 'type' => 'error']);
        }

        $validated = $request->validate([
            'action'          => 'required',
            'prompt'          => 'required',
            'photo'           => 'required_if:action,klingImage,klingV21',
            'aspect_ratio'    => 'required_if:action,veo3,veo3-fast',
            'enhance_prompt'  => 'sometimes',
            'generate_audio'  => 'sometimes',
            'seed'            => 'sometimes|nullable|integer',
            'negative_prompt' => 'sometimes|nullable|string',
        ]);

        $action = $request->get('action');
        $model = $action;
        $seconds = 0;
        if ($action === 'sora') {
            $model = EntityEnum::fromSlug($request->input('sora_model') ?? EntityEnum::SORA_2->value);
            $seconds = (int) $request->input('sora_seconds', 4);
            $driver = Entity::driver($model)->inputSecond($seconds)->calculateCredit();
        } elseif ($action === 'veo3.1') {
            $driver = Entity::driver($this->detectVeo31EntityEnum($request->get('veo3_1_mode')))->inputVideoCount(1)->calculateCredit();
        } elseif ($action === 'kling25Turbo') {
            $driver = Entity::driver($this->detectKling25EntityEnum($request->get('kling25_mode')))->inputVideoCount(1)->calculateCredit();
        } else {
            $driver = Entity::driver(EntityEnum::fromSlug($action))->inputVideoCount(1)->calculateCredit();
        }

        try {
            $driver->redirectIfNoCreditBalance();
        } catch (Exception $e) {
            return redirect()->back()->with([
                'message' => $e->getMessage(),
                'type'    => 'error',
            ]);
        }

        $prompt = $request->get('prompt');
        $userId = auth()->id();

        switch ($action) {
            case 'sora':
                $image = $request->file('photo');
                $param = [
                    'prompt'    => $prompt,
                    'model'     => $model->value,
                    'seconds'   => $seconds,
                    'size'      => $request->input('sora_size') ?? '720x1280',
                    'image_url' => null,
                ];

                if ($image && $image->isValid()) {
                    $param['image_url'] = $image;
                }

                $response = SoraService::generate($param);

                if (isset($response['error'])) {
                    return back()->with([
                        'message' => $response['error']['message'] ?? __('Generation Failed'),
                        'type'    => 'error',
                    ]);
                }

                if (isset($response['status']) && $response['status'] === 'failed') {
                    return back()->with(['message' => $response['message'] ?? __('Generation Failed'), 'type' => 'error']);
                }

                $this->createUserFall($userId, $prompt, $action, $response, $param['image_url']);
                $driver->decreaseCredit();

                return back()->with(['message' => __('Created Successfully.'), 'type' => 'success']);
            case 'klingImage':
            case 'klingV21':
            case 'haiper':
                $image = $request->file('photo');
                $name = Str::random(12) . '.' . $image?->guessExtension();
                Storage::disk('public')->put($name, file_get_contents($image->getRealPath()));
                $url = Helper::parseUrl(config('app.url') . '/uploads/' . $name);

                $response = FalAIService::{$action . 'Generate'}($prompt, $url);

                if (isset($response['status']) && $response['status'] === 'error') {
                    return back()->with(['message' => $response['message'], 'type' => 'error']);
                }

                $this->createUserFall($userId, $prompt, $action, $response, $url);
                $driver->decreaseCredit();

                return back()->with(['message' => 'Created Successfully.', 'type' => 'success']);
            case 'luma-dream-machine':
            case 'kling':
            case 'minimax':
                $response = FalAIService::minimaxGenerate($prompt);

                $this->createUserFall($userId, $prompt, $action, $response);
                $driver->decreaseCredit();

                return back()->with(['message' => 'Created Successfully.', 'type' => 'success']);
            case 'veo2':
                $response = FalAIService::veo2Generate($prompt);
                if ($response->failed()) {
                    return back()->with([
                        'message' => $response->status() . ' ' . $response->reason() . ': ' .
                            $response->json('detail', __('Unknown error occurred')),
                        'type' => 'error',
                    ]);
                }
                $jsonRes = $response->json();
                if (isset($jsonRes['status']) && $jsonRes['status'] === 'error') {
                    return back()->with(['message' => $jsonRes['message'], 'type' => 'error']);
                }
                $this->createUserFall($userId, $prompt, $action, $jsonRes);
                $driver->decreaseCredit();

                return back()->with(['message' => 'Created Successfully.', 'type' => 'success']);
            case 'veo3':
            case 'veo3-fast':
                $validated = array_filter($validated, static function ($value) {
                    return ! is_null($value) && $value !== '';
                });

                if (isset($validated['generate_audio'])) {
                    $validated['generate_audio'] = $validated['generate_audio'] == 'on' ? true : false;
                }

                if (isset($validated['enhance_prompt'])) {
                    $validated['enhance_prompt'] = $validated['enhance_prompt'] == 'on' ? true : false;
                }

                unset($validated['action']);

                $service = new PackageFalAIService(ApiHelper::setFalAIKey());
                $response = $service->textToVideoModel($action == 'veo3' ? EntityEnum::VEO_3 : EntityEnum::VEO_3_FAST)->submit($validated);
                $resData = $response->getData();
                if (isset($resData->status) && $resData->status === 'error') {
                    return back()->with(['message' => $resData->message ?? 'Unexpected issue happen', 'type' => 'error']);
                }
                $this->createUserFall($userId, $prompt, $action, (array) $resData->resData);
                $driver->decreaseCredit();

                return back()->with(['message' => 'Created Successfully.', 'type' => 'success']);
            case 'veo3.1':
                $mode = $request->input('veo3_1_mode');

                // Convert checkbox values to boolean BEFORE validation
                if ($request->has('generate_audio')) {
                    $request->merge([
                        'generate_audio' => $request->input('generate_audio') === 'on' ? true : false,
                    ]);
                }
                if ($request->has('enhance_prompt')) {
                    $request->merge([
                        'enhance_prompt' => $request->input('enhance_prompt') === 'on' ? true : false,
                    ]);
                }
                if ($request->has('auto_fix')) {
                    $request->merge([
                        'auto_fix' => $request->input('auto_fix') === 'on' ? true : false,
                    ]);
                }

                // Base validation rules
                $validationRules = [
                    'veo3_1_mode'     => 'required|in:text-to-video,text-to-video-fast,first-last-frame-to-video,first-last-frame-to-video-fast,image-to-video,image-to-video-fast,reference-to-video',
                    'generate_audio'  => 'sometimes|boolean',
                ];

                // Duration validation - depends on mode
                if (in_array($mode, ['text-to-video', 'text-to-video-fast'])) {
                    $validationRules['duration'] = 'required|in:4s,6s,8s';
                } else {
                    $validationRules['duration'] = 'required|in:8s';
                }

                // Resolution validation - all modes
                $validationRules['resolution'] = 'required|in:720p,1080p';

                // Aspect ratio validation - depends on mode
                if (in_array($mode, ['text-to-video', 'text-to-video-fast'])) {
                    $validationRules['aspect_ratio'] = 'required|in:16:9,9:16,1:1';
                } elseif (in_array($mode, ['first-last-frame-to-video', 'first-last-frame-to-video-fast'])) {
                    $validationRules['aspect_ratio'] = 'required|in:auto,16:9,9:16,1:1';
                } elseif (in_array($mode, ['image-to-video', 'image-to-video-fast'])) {
                    $validationRules['aspect_ratio'] = 'required|in:16:9,9:16';
                }
                // reference-to-video mode doesn't have aspect_ratio

                // Advanced options - only for text-to-video modes
                if (in_array($mode, ['text-to-video', 'text-to-video-fast'])) {
                    $validationRules['enhance_prompt'] = 'sometimes|boolean';
                    $validationRules['auto_fix'] = 'sometimes|boolean';
                    $validationRules['seed'] = 'sometimes|nullable|integer';
                    $validationRules['negative_prompt'] = 'sometimes|nullable|string|max:1000';
                }

                // File validation for different modes
                if (in_array($mode, ['image-to-video', 'image-to-video-fast'])) {
                    $validationRules['image_url'] = 'required|file|mimes:jpg,jpeg,png|max:10240';
                }

                if (in_array($mode, ['first-last-frame-to-video', 'first-last-frame-to-video-fast'])) {
                    $validationRules['first_frame_image_url'] = 'required|file|mimes:jpg,jpeg,png|max:10240';
                    $validationRules['last_frame_image_url'] = 'required|file|mimes:jpg,jpeg,png|max:10240';
                }

                if ($mode === 'reference-to-video') {
                    // Manually check and limit the array before validation
                    if ($request->hasFile('image_urls')) {
                        $files = $request->file('image_urls');

                        // Remove duplicates based on file content hash
                        $uniqueFiles = [];
                        $seenHashes = [];

                        foreach ($files as $file) {
                            $hash = md5_file($file->getRealPath());
                            if (! in_array($hash, $seenHashes)) {
                                $seenHashes[] = $hash;
                                $uniqueFiles[] = $file;
                            }
                        }

                        // Take only first 3 files if more are submitted
                        $limitedFiles = array_slice($uniqueFiles, 0, 3);
                        $request->files->set('image_urls', $limitedFiles);
                    }

                    $validationRules['image_urls'] = 'required|array|min:1|max:3';
                    $validationRules['image_urls.*'] = 'file|mimes:jpg,jpeg,png|max:10240';
                }

                $veo31Validation = $request->validate($validationRules);

                // Build payload
                $payload = [
                    'prompt'       => $prompt,
                    'mode'         => $mode,
                    'duration'     => $veo31Validation['duration'] ?? '8s',
                    'resolution'   => $veo31Validation['resolution'] ?? '720p',
                ];

                // Add aspect_ratio only if it exists for this mode
                if (isset($veo31Validation['aspect_ratio'])) {
                    $payload['aspect_ratio'] = $veo31Validation['aspect_ratio'];
                }

                // Generate audio (default true if not specified)
                $payload['generate_audio'] = $veo31Validation['generate_audio'] ?? true;

                // Advanced options - only for text-to-video modes
                if (in_array($mode, ['text-to-video', 'text-to-video-fast'])) {
                    if (isset($veo31Validation['enhance_prompt'])) {
                        $payload['enhance_prompt'] = $veo31Validation['enhance_prompt'];
                    }
                    if (isset($veo31Validation['auto_fix'])) {
                        $payload['auto_fix'] = $veo31Validation['auto_fix'];
                    }
                    if (isset($veo31Validation['seed']) && $veo31Validation['seed'] !== null) {
                        $payload['seed'] = (int) $veo31Validation['seed'];
                    }
                    if (isset($veo31Validation['negative_prompt']) && $veo31Validation['negative_prompt'] !== null) {
                        $payload['negative_prompt'] = $veo31Validation['negative_prompt'];
                    }
                }

                try {
                    switch ($mode) {
                        case 'image-to-video':
                        case 'image-to-video-fast':
                            // Single image upload
                            if ($request->hasFile('image_url')) {
                                $image = $request->file('image_url');
                                $imageName = Str::random(12) . '.' . $image->guessExtension();
                                Storage::disk('public')->put($imageName, file_get_contents($image->getRealPath()));
                                $payload['image_url'] = Helper::parseUrl(config('app.url') . '/uploads/' . $imageName);
                            }

                            break;

                        case 'first-last-frame-to-video':
                        case 'first-last-frame-to-video-fast':
                            // First and last frame uploads
                            if ($request->hasFile('first_frame_image_url')) {
                                $firstFrame = $request->file('first_frame_image_url');
                                $firstName = Str::random(12) . '.' . $firstFrame->guessExtension();
                                Storage::disk('public')->put($firstName, file_get_contents($firstFrame->getRealPath()));
                                $payload['first_frame_image_url'] = Helper::parseUrl(config('app.url') . '/uploads/' . $firstName);
                            }

                            if ($request->hasFile('last_frame_image_url')) {
                                $lastFrame = $request->file('last_frame_image_url');
                                $lastName = Str::random(12) . '.' . $lastFrame->guessExtension();
                                Storage::disk('public')->put($lastName, file_get_contents($lastFrame->getRealPath()));
                                $payload['last_frame_image_url'] = Helper::parseUrl(config('app.url') . '/uploads/' . $lastName);
                            }

                            break;

                        case 'reference-to-video':
                            // Multiple reference images (1-3)
                            if ($request->hasFile('image_urls')) {
                                $referenceImages = [];
                                $files = $request->file('image_urls');

                                foreach ($files as $refImage) {
                                    $refName = Str::random(12) . '.' . $refImage->guessExtension();
                                    Storage::disk('public')->put($refName, file_get_contents($refImage->getRealPath()));
                                    $referenceImages[] = Helper::parseUrl(config('app.url') . '/uploads/' . $refName);
                                }

                                $payload['image_urls'] = $referenceImages;
                            }

                            break;
                    }
                } catch (Exception $e) {
                    return back()->with([
                        'message' => 'File upload error: ' . $e->getMessage(),
                        'type'    => 'error',
                    ]);
                }

                // Remove null values and empty arrays
                $payload = array_filter($payload, static function ($value) {
                    return ! is_null($value) && $value !== '' && (! is_array($value) || ! empty($value));
                });

                // Submit to VEO 3.1
                $service = new PackageFalAIService(ApiHelper::setFalAIKey());
                $response = $service->textToVideoModel($this->detectVeo31EntityEnum($mode))->submit($payload);
                $resData = $response->getData();

                if (isset($resData->status) && $resData->status === 'error') {
                    return back()->with([
                        'message' => $resData->message ?? 'VEO 3.1 generation failed',
                        'type'    => 'error',
                    ]);
                }

                $this->createUserFall($userId, $prompt, ($action . '/' . $mode), (array) $resData->resData);
                $driver->decreaseCredit();

                return back()->with([
                    'message' => 'VEO 3.1 video generation started successfully.',
                    'type'    => 'success',
                ]);

                break;
            case 'kling25Turbo':
                $kling25Validation = $request->validate([
                    'kling25_mode'              => 'required|in:text-to-video-pro,image-to-video,image-to-video-pro',
                    'kling25turbo_duration'     => 'required|in:5,10',
                    'kling25turbo_aspect_ratio' => 'required|in:16:9,9:16,1:1',
                    'camera_movement'           => 'sometimes|nullable|in:horizontal,vertical,pan,tilt,zoom,roll',
                    'seed'                      => 'sometimes|nullable|integer|min:0|max:2147483647',
                    'negative_prompt'           => 'sometimes|nullable|string',
                    'cfg_scale'                 => 'sometimes|nullable|numeric|min:0|max:1',
                    'loop'                      => 'sometimes',
                    'image_url'                 => 'required_if:kling25_mode,image-to-video,image-to-video-pro|file|mimes:jpg,jpeg,png',
                ]);

                $mode = $kling25Validation['kling25_mode'];
                // Build payload
                $payload = [
                    'prompt'       => $prompt,
                    'duration'     => $kling25Validation['kling25turbo_duration'] ?? '5',
                    'aspect_ratio' => $kling25Validation['kling25turbo_aspect_ratio'] ?? '16:9',
                ];

                // Handle image upload for image-to-video modes
                if (in_array($mode, ['image-to-video', 'image-to-video-pro']) && $request->hasFile('image_url')) {
                    try {
                        $image = $request->file('image_url');
                        $imageName = Str::random(12) . '.' . $image->guessExtension();
                        Storage::disk('public')->put($imageName, file_get_contents($image->getRealPath()));
                        $payload['image_url'] = Helper::parseUrl(config('app.url') . '/uploads/' . $imageName);
                    } catch (Exception $e) {
                        return back()->with([
                            'message' => 'Image upload error: ' . $e->getMessage(),
                            'type'    => 'error',
                        ]);
                    }
                }

                // Add optional parameters for text-to-video modes
                if ($mode === 'text-to-video-pro') {
                    if (! empty($kling25Validation['camera_movement'])) {
                        $payload['camera_movement'] = [
                            'type' => $kling25Validation['camera_movement'],
                        ];
                    }

                    if (isset($kling25Validation['cfg_scale'])) {
                        $payload['cfg_scale'] = (float) $kling25Validation['cfg_scale'];
                    }
                }

                // Add seed if provided
                if (isset($kling25Validation['seed'])) {
                    $payload['seed'] = (int) $kling25Validation['seed'];
                }

                // Add negative prompt if provided
                if (! empty($kling25Validation['negative_prompt'])) {
                    $payload['negative_prompt'] = $kling25Validation['negative_prompt'];
                }

                // Add loop parameter for Pro modes only
                if (isset($kling25Validation['loop']) && str_contains($mode, 'pro')) {
                    $payload['loop'] = $kling25Validation['loop'] === 'on';
                }

                // Remove null/empty values
                $payload = array_filter($payload, static function ($value) {
                    return ! is_null($value) && $value !== '' && (! is_array($value) || ! empty($value));
                });

                // Submit to Kling 2.5 Turbo API
                $service = new PackageFalAIService(ApiHelper::setFalAIKey());
                $response = $service->textToVideoModel($this->detectKling25EntityEnum($mode))->submit($payload);
                $resData = $response->getData();

                if (isset($resData->status) && $resData->status === 'error') {
                    return back()->with([
                        'message' => $resData->message ?? __('Kling 2.5 Turbo generation failed'),
                        'type'    => 'error',
                    ]);
                }

                $this->createUserFall($userId, $prompt, ($action . '/' . $mode), (array) $resData->resData, $payload['image_url'] ?? null);
                $driver->decreaseCredit();

                return back()->with([
                    'message' => __('Kling 2.5 Turbo video generation started successfully.'),
                    'type'    => 'success',
                ]);
            default:
                return back()->with(['message' => __('Api key Error.'), 'type' => 'error']);
        }
    }

    private function createUserFall($userId, $prompt, $action, $response, $imageUrl = null): void
    {
        UserFall::create([
            'user_id'          => $userId,
            'prompt'           => $prompt,
            'prompt_image_url' => $imageUrl,
            'status'           => $response['status'] ?? 'IN_QUEUE',
            'request_id'       => $response['request_id'] ?? $response['id'] ?? null,
            'response_url'     => $response['response_url'] ?? null,
            'model'            => $action,
        ]);
    }

    public function delete(string $id): RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return back()->with(['message' => 'This feature is disabled in demo mode.', 'type' => 'error']);
        }

        $model = UserFall::query()->findOrFail($id);

        $model->delete();

        return back()->with(['message' => 'Deleted Successfully.', 'type' => 'success']);
    }

    public function checkVideoStatus(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $ids = (array) $request->get('ids', []);

        $entries = UserFall::where('user_id', $userId)
            ->where('status', '!=', 'complete')
            ->whereIn('id', $ids)
            ->get();

        if ($entries->isEmpty()) {
            return response()->json(['data' => []]);
        }

        // Delete error entries in one go
        $errorIds = $entries->where('status', 'error')->pluck('id');
        if ($errorIds->isNotEmpty()) {
            UserFall::whereIn('id', $errorIds)->delete();
            $entries = $entries->reject(fn ($e) => $errorIds->contains($e->id));
        }

        $data = collect();

        foreach ($entries as $entry) {
            $result = match (true) {
                $entry->model === 'sora'                              => $this->handleSoraEntry($entry),
                str_starts_with($entry->model ?? '', 'veo3.1/')       => $this->handleVeoEntry($entry),
                str_starts_with($entry->model ?? '', 'kling25Turbo/') => $this->handleKling25Entry($entry),
                default                                               => $this->handleFalEntry($entry),
            };

            if ($result) {
                $data->push($result);
            }
        }

        return response()->json(['data' => $data]);
    }

    private function handleSoraEntry($entry): ?array
    {
        $response = SoraService::getStatus($entry->request_id);
        $status = $response['status'] ?? null;

        if ($status === 'completed') {
            $vidUrl = SoraService::getVideo($entry->request_id);
            if ($vidUrl) {
                $entry->update(['status' => 'complete', 'video_url' => $vidUrl]);

                return $this->renderVideoItem($entry, $vidUrl);
            }
            $entry->delete();
        }

        if ($status === 'failed') {
            $entry->delete();
        }

        return null;
    }

    private function handleVeoEntry($entry): ?array
    {
        // Extract mode from model string (e.g., "veo3.1/text-to-video-fast" -> "text-to-video-fast")
        $mode = str_replace('veo3.1/', '', $entry->model);
        $entity = $this->detectVeo31EntityEnum($mode);
        $service = new PackageFalAIService(ApiHelper::setFalAIKey());
        $check = $service->textToVideoModel($entity)->checkStatus($entry->request_id)->getData();
        $status = $check->resData->status ?? null;

        if ($status === 'NOT_FOUND') {
            $entry->delete();

            return null;
        }

        if ($status !== 'COMPLETED') {
            return null;
        }

        $result = $service->textToVideoModel($entity)->getResult($entry->request_id)->getData();

        if (($result->status ?? null) === 'success') {
            $videoUrl = $result->resData->video->url ?? null;
            if ($videoUrl) {
                $entry->update(['status' => 'complete', 'video_url' => $videoUrl]);

                return $this->renderVideoItem($entry, $videoUrl);
            }
        }

        if (in_array(($result->status ?? null), ['failed', 'error'])) {
            $entry->delete();
        }

        return null;
    }

    private function handleKling25Entry($entry): ?array
    {
        // Extract mode from model string (e.g., "kling25Turbo/text-to-video" -> "text-to-video")
        $mode = str_replace('kling25Turbo/', '', $entry->model);
        $entity = $this->detectKling25EntityEnum($mode);

        $service = new PackageFalAIService(ApiHelper::setFalAIKey());
        $check = $service->textToVideoModel($entity)->checkStatus($entry->request_id)->getData();
        $status = $check->resData->status ?? null;

        if ($status !== 'COMPLETED') {
            return null;
        }

        $result = $service->textToVideoModel($entity)->getResult($entry->request_id)->getData();

        if (($result->status ?? null) === 'success') {
            $videoUrl = $result->resData->video->url ?? null;
            if ($videoUrl) {
                $entry->update(['status' => 'complete', 'video_url' => $videoUrl]);

                return $this->renderVideoItem($entry, $videoUrl);
            }
        }

        if (in_array(($result->status ?? null), ['failed', 'error'])) {
            $entry->delete();
        }

        return null;
    }

    private function handleFalEntry($entry): ?array
    {
        $response = FalAIService::getStatus($entry->response_url);

        if (! empty($response['video']['url'])) {
            $url = $response['video']['url'];
            $entry->update(['status' => 'complete', 'video_url' => $url]);

            return $this->renderVideoItem($entry, $url);
        }

        $detail = $response['detail'] ?? null;

        // Handle failed or invalid responses
        if (
            in_array($detail, [
                'Internal Server Error',
                'Luma API timed out',
                "Luma API failed: generation.state='failed' generation.failure_reason='400: prompt not allowed because advanced moderation failed'",
            ]) ||
            (isset($detail[0]['type']) && $detail[0]['type'] === 'image_load_error')
        ) {
            $entry->delete();
        }

        return null;
    }

    private function renderVideoItem($entry, string $url): array
    {
        $entry->video_url = $url;
        $entry->status = 'complete';

        return [
            'divId' => "video-{$entry->id}",
            'html'  => view('ai-video-pro::video-item', ['entry' => $entry])->render(),
        ];
    }

    private function detectKling25EntityEnum(?string $mode): EntityEnum
    {
        return match ($mode) {
            'image-to-video'     => EntityEnum::KLING_2_5_TURBO_STANDARD_ITV,
            'image-to-video-pro' => EntityEnum::KLING_2_5_TURBO_PRO_ITV,
            default              => EntityEnum::KLING_2_5_TURBO_PRO_TTV, // text-to-video as default
        };
    }

    private function detectVeo31EntityEnum(?string $mode): EntityEnum
    {
        return match ($mode) {
            'image-to-video'                 => EntityEnum::VEO_3_1_IMAGE_TO_VIDEO,
            'image-to-video-fast'            => EntityEnum::VEO_3_1_IMAGE_TO_VIDEO_FAST,
            'first-last-frame-to-video'      => EntityEnum::VEO_3_1_FIRST_LAST_FRAME_TO_VIDEO,
            'first-last-frame-to-video-fast' => EntityEnum::VEO_3_1_FIRST_LAST_FRAME_TO_VIDEO_FAST,
            'reference-to-video'             => EntityEnum::VEO_3_1_REFERENCE_TO_VIDEO,
            'text-to-video-fast'             => EntityEnum::VEO_3_1_TEXT_TO_VIDEO_FAST,
            default                          => EntityEnum::VEO_3_1_TEXT_TO_VIDEO,
        };
    }
}
