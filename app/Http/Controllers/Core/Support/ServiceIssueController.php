<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\ServiceIssue;
use App\Models\Support\ServiceIssueMessage;
use App\Services\Support\SupportLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceIssueController extends Controller
{
    private const DIRECTION_AGENT = 'agent';
    private const DIRECTION_USER = 'user';
    private const STATUS_RESOLVED = 'resolved';

    public function __construct(private SupportLifecycleService $lifecycle) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $issues = ServiceIssue::query()
            ->when(
                $user && ! $user->isAdmin(),
                function ($query) use ($user) {
                    $query->where(static function ($builder) use ($user) {
                        $builder->where('assigned_to', $user->id)
                            ->orWhere('user_id', $user->id);
                    });
                }
            )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->latest()
            ->paginate(15);

        return response()->json($issues);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject'       => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'priority'      => ['nullable', 'string', 'max:50'],
            'status'        => ['nullable', 'string', 'max:50'],
            'assigned_to'   => ['nullable', 'integer'],
            'source'        => ['nullable', 'string', 'max:100'],
            'external_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $issue = ServiceIssue::create([
            'company_id'         => $user?->company_id,
            'user_id'            => $user?->id,
            'subject'            => $data['subject'],
            'description'        => $data['description'] ?? null,
            'priority'           => $data['priority'] ?? 'medium',
            'status'             => $data['status'] ?? 'open',
            'assigned_to'        => $data['assigned_to'] ?? null,
            'source'             => $data['source'] ?? null,
            'external_reference' => $data['external_reference'] ?? null,
        ]);

        return response()->json($issue, 201);
    }

    public function show(ServiceIssue $issue): JsonResponse
    {
        $issue->load(['messages', 'assignedAgent', 'requester']);

        return response()->json($issue);
    }

    public function update(Request $request, ServiceIssue $issue): JsonResponse
    {
        $data = $request->validate([
            'subject'     => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority'    => ['sometimes', 'string', 'max:50'],
            'status'      => ['sometimes', 'string', 'max:50'],
            'assigned_to' => ['nullable', 'integer'],
        ]);

        $issue->fill($data);

        if (array_key_exists('status', $data) && $data['status'] === self::STATUS_RESOLVED) {
            $issue->resolved_at = now();
        }

        $issue->save();

        return response()->json($issue->fresh());
    }

    public function destroy(ServiceIssue $issue): JsonResponse
    {
        $issue->delete();

        return response()->json(['deleted' => true]);
    }

    public function reply(Request $request, ServiceIssue $issue): JsonResponse
    {
        $message = $this->storeMessage($request, $issue, false);

        $user = $request->user();
        $direction = $user && method_exists($user, 'isAdmin') && $user->isAdmin()
            ? self::DIRECTION_AGENT
            : self::DIRECTION_USER;

        $this->lifecycle->processReplies($issue, $direction);

        return response()->json($message, 201);
    }

    public function note(Request $request, ServiceIssue $issue): JsonResponse
    {
        $message = $this->storeMessage($request, $issue, true);

        return response()->json($message, 201);
    }

    protected function storeMessage(Request $request, ServiceIssue $issue, bool $internal): ServiceIssueMessage
    {
        $data = $request->validate([
            'message'       => ['nullable', 'string'],
            'attachments'   => ['nullable', 'array'],
            'attachments.*' => ['string'],
        ]);

        /** @var \App\Models\User|null $user */
        $user = $request->user();

        return $issue->messages()->create([
            'company_id'   => $issue->company_id ?? $user?->company_id,
            'user_id'      => $user?->id,
            'is_internal'  => $internal,
            'message'      => $data['message'] ?? null,
            'attachments'  => $data['attachments'] ?? [],
        ]);
    }
}
