<?php
namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AIConverseController extends BaseController
{
    public function index(Request $request)
    {
        // Basic entitlement check via DB (worksuite-style); adjust helper if your app exposes one.
        $companyId = (int) (tenant()->id ?? company()->id ?? 0); // falls back if helpers exist
        $entitled = true;
        try {
            $entitled = DB::table('module_settings')
                ->where('company_id', $companyId)
                ->where('module_name', 'aiconverse')
                ->where('status', 'active')
                ->exists();
        } catch (\Throwable $e) {
            // If table missing in dev, allow index to render
            $entitled = true;
        }

        if (!$entitled) {
            abort(403, 'AIConverse not enabled for your plan.');
        }

        return view('titantalk::index');
    }
}
