<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AdminSettingsController
 *
 * Provides a unified settings management surface for the Titan Admin panel.
 * Reads/writes via AdminSettingsService which wraps the `settings` and
 * `settings_two` tables.
 *
 * Routes: titan.admin.settings.*
 */
class AdminSettingsController extends Controller
{
    public function __construct(
        protected AdminSettingsService $settingsService,
    ) {
    }

    public function index(): View
    {
        $settings = $this->settingsService->all();

        return view('panel.admin.settings.general', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->except(['_token', '_method']);

        $this->settingsService->bulkUpdate($data);

        return redirect()->route('titan.admin.settings.index')
            ->with('success', __('Settings saved.'));
    }
}
