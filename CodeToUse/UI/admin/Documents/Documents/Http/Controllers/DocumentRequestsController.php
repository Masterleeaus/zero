<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Documents\Entities\DocumentRequest;
use Modules\Documents\Services\DocumentRequestService;

class DocumentRequestsController extends Controller
{
    public function __construct(private DocumentRequestService $service) {}

    public function index()
    {
        $this->authorize('viewAny', DocumentRequest::class);

        $requests = DocumentRequest::query()->latest()->paginate(25);

        return view('documents::requests.index', compact('requests'));
    }

    public function create()
    {
        $this->authorize('create', DocumentRequest::class);
        return view('documents::requests.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', DocumentRequest::class);

        $data = $request->validate([
            'title' => 'required|string|max:190',
            'recipient_email' => 'nullable|email|max:190',
            'recipient_name' => 'nullable|string|max:190',
            'message' => 'nullable|string',
            'due_at' => 'nullable|date',
            'send_email' => 'nullable|boolean',
        ]);

        $req = $this->service->createRequest($data);

        if (!empty($data['send_email'])) {
            $this->authorize('send', $req);
            $this->service->sendRequest($req);
        }

        return redirect()->route('documents.requests.show', $req)->with('success', __('Request created.'));
    }

    public function show(DocumentRequest $request)
    {
        $this->authorize('view', $request);
        $request->load('uploads');
        return view('documents::requests.show', ['req' => $request]);
    }

    public function cancel(DocumentRequest $request)
    {
        $this->authorize('cancel', $request);

        $request->forceFill(['status' => 'cancelled', 'cancelled_at' => now()])->save();

        return back()->with('success', __('Request cancelled.'));
    }

    public function resend(DocumentRequest $request)
    {
        $this->authorize('send', $request);

        $this->service->sendRequest($request);

        return back()->with('success', __('Request sent.'));
    }
}
