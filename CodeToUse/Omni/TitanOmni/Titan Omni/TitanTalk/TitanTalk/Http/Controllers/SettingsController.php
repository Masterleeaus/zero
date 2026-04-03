<?php

namespace Modules\TitanTalk\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanTalk\Models\Channel;

class SettingsController extends Controller
{
    protected function resolveTenantId(): ?int
    {
        // Worksuite SaaS often uses a "company" context (multi-company / multi-tenant).
        // We try a few safe patterns without hard dependency on helper functions.
        try {
            if (function_exists('company')) {
                $c = company();
                if (is_object($c) && isset($c->id)) return (int) $c->id;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $user = auth()->user();
        if (is_object($user)) {
            if (isset($user->company_id)) return (int) $user->company_id;
            if (isset($user->company) && is_object($user->company) && isset($user->company->id)) {
                return (int) $user->company->id;
            }
        }

        return null;
    }

    public function index()
    {
        $tenantId = $this->resolveTenantId();

        // Ensure SMS and Email channels exist (tenant-scoped; falls back to global when tenant unknown)
        $sms = Channel::firstOrCreate(
            ['tenant_id' => $tenantId, 'driver' => 'sms'],
            ['name' => 'SMS', 'config' => [], 'enabled' => false]
        );

        $email = Channel::firstOrCreate(
            ['tenant_id' => $tenantId, 'driver' => 'email'],
            ['name' => 'Email', 'config' => [], 'enabled' => false]
        );

        return view('titantalk::settings.index', compact('sms', 'email'));
    }

    public function save(Request $request)
    {
        $tenantId = $this->resolveTenantId();

        // SMS settings
        $sms = Channel::firstOrCreate(
            ['tenant_id' => $tenantId, 'driver' => 'sms'],
            ['name' => 'SMS', 'config' => [], 'enabled' => false]
        );

        $sms->enabled = $request->boolean('sms_enabled');
        $sms->name    = 'SMS';
        $sms->config = [
            'provider'   => $request->input('sms_provider'),
            'from'       => $request->input('sms_from'),
            'api_key'    => $request->input('sms_api_key'),
            'api_secret' => $request->input('sms_api_secret'),
            'extra'      => $request->input('sms_extra'),
        ];
        $sms->save();

        // Email settings
        $email = Channel::firstOrCreate(
            ['tenant_id' => $tenantId, 'driver' => 'email'],
            ['name' => 'Email', 'config' => [], 'enabled' => false]
        );

        $email->enabled = $request->boolean('email_enabled');
        $email->name    = 'Email';
        $email->config = [
            'host'         => $request->input('email_host'),
            'port'         => $request->input('email_port'),
            'encryption'   => $request->input('email_encryption'),
            'username'     => $request->input('email_username'),
            'password'     => $request->input('email_password'),
            'from_name'    => $request->input('email_from_name'),
            'from_address' => $request->input('email_from_address'),
        ];
        $email->save();

        return redirect()
            ->route('titantalk.settings')
            ->with('success', 'Titan Talk channel settings updated.');
    }
}
