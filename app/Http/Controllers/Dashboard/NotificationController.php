<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ?->notifications()
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->paginate(20);

        return view('default.panel.user.notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request)
    {
        if (Helper::appIsNotDemo()) {
            if ($request->has('id')) {
                $notification = $request->user()->notifications()
                    ->where('company_id', $request->user()->company_id)
                    ->where('id', $request->id)->first();
                if ($notification) {
                    $notification->markAsRead();
                }

                return $request->wantsJson()
                    ? response()->json(['success' => true])
                    : back()->with('message', __('Marked as read'));
            }

            $request->user()->unreadNotifications->markAsRead();

            return $request->wantsJson()
                ? response()->json(['success' => true])
                : back()->with('message', __('Marked as read'));
        }
    }
}
