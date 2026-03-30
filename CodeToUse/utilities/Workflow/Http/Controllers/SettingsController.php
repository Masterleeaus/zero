<?php

namespace Modules\Workflow\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Modules\Workflow\Services\SettingsRepository;

class SettingsController extends Controller
{
    public function __construct(protected SettingsRepository $repo) {}


    public function index()
    {
        $companyId = function_exists('company') && company() ? company()->id : null;
        $db = $this->repo->getAll($companyId);
        $defaults = config('workflow.settings', []);
        $settings = array_replace_recursive($defaults, $db);
        return view('workflow::settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'pagination' => 'required|integer|min:5|max:200',
            'default_status' => 'required|in:active,inactive',
            'features' => 'array',
            'features.api' => 'nullable|boolean',
            'features.approvals' => 'nullable|boolean',
        ]);

        $companyId = function_exists('company') && company() ? company()->id : null;
        $userId = auth()->check() ? auth()->id() : null;

        $payload = [
            'pagination' => (int)($data['pagination'] ?? 20),
            'default_status' => $data['default_status'] ?? 'active',
            'features' => [
                'api' => (bool)($data['features']['api'] ?? false),
                'approvals' => (bool)($data['features']['approvals'] ?? false),
            ],
        ];

        $this->repo->setMany($companyId, $payload, $userId);

        return redirect()->back()->with('status', 'Settings saved.');
    }
}
