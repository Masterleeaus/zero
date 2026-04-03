<?php
namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Modules\TitanTalk\Models\Conversation;
use Modules\TitanTalk\Models\Message;

class DashboardController extends Controller
{
    public function index()
    {
        $today      = Carbon::today();
        $last30Days = Carbon::today()->subDays(29);

        $totalConversations = Conversation::count();
        $conversationsLast7 = Conversation::where('created_at', '>=', Carbon::today()->subDays(7))->count();
        $messagesToday      = Message::whereDate('created_at', $today)->count();

        $byChannel = Conversation::selectRaw('channel, COUNT(*) as total')
            ->groupBy('channel')
            ->pluck('total', 'channel')
            ->toArray();

        $dailyConversations = Conversation::where('created_at', '>=', $last30Days)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        return view('titantalk::dashboard.index', compact(
            'totalConversations',
            'conversationsLast7',
            'messagesToday',
            'byChannel',
            'dailyConversations'
        ));
    }
}
