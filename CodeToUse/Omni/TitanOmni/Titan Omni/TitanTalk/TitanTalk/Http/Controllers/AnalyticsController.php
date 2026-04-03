<?php
namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function dashboard()
    {
        // Simple KPIs; adapt for multi-tenant
        $conversations = DB::table('ai_converse_conversations')->count();
        $messages = DB::table('ai_converse_messages')->count();
        $today = date('Y-m-d');
        $todayMsgs = DB::table('ai_converse_messages')->whereDate('created_at', $today)->count();

        $byChannel = DB::table('ai_converse_conversations')
            ->select('channel', DB::raw('count(*) as c'))
            ->groupBy('channel')->get();

        $latency = DB::table('ai_converse_provider_logs')
            ->select(DB::raw('avg(JSON_EXTRACT(payload, "$.latency_ms")) as ms'))->value('ms');

        return view('titantalk::analytics.dashboard', compact('conversations','messages','todayMsgs','byChannel','latency'));
    }
}
