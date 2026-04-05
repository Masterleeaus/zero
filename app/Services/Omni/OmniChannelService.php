<?php

declare(strict_types=1);

namespace App\Services\Omni;

use App\Events\Omni\OmniChannelDeregistered;
use App\Events\Omni\OmniChannelRegistered;
use App\Models\Omni\OmniChannelBridge;
use Illuminate\Support\Facades\Log;

/**
 * OmniChannelService — channel bridge lifecycle management.
 *
 * Registers and deregisters per-company channel credentials.
 * Credentials are stored encrypted; this service never returns raw secrets.
 */
class OmniChannelService
{
    /**
     * Register a new channel bridge for a company.
     *
     * @param  array<string, mixed>  $config
     */
    public function register(int $companyId, string $channelType, array $config): OmniChannelBridge
    {
        $bridge = OmniChannelBridge::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('channel_type', $channelType)
            ->where('agent_id', $config['agent_id'] ?? null)
            ->first();

        if ($bridge) {
            $bridge->update(array_merge($config, [
                'company_id'   => $companyId,
                'channel_type' => $channelType,
                'is_active'    => true,
                'verified_at'  => null,
            ]));

            Log::info('omni.channel.updated', compact('companyId', 'channelType'));
            return $bridge->fresh();
        }

        $bridge = OmniChannelBridge::create(array_merge($config, [
            'company_id'   => $companyId,
            'channel_type' => $channelType,
            'is_active'    => true,
        ]));

        event(new OmniChannelRegistered($bridge));

        Log::info('omni.channel.registered', [
            'company_id'   => $companyId,
            'channel_type' => $channelType,
            'bridge_id'    => $bridge->id,
        ]);

        return $bridge;
    }

    /**
     * Deregister (soft-disable) a channel bridge.
     */
    public function deregister(OmniChannelBridge $bridge): OmniChannelBridge
    {
        $bridge->update([
            'is_active'   => false,
            'verified_at' => null,
        ]);

        event(new OmniChannelDeregistered($bridge));

        Log::info('omni.channel.deregistered', [
            'bridge_id'    => $bridge->id,
            'company_id'   => $bridge->company_id,
            'channel_type' => $bridge->channel_type,
        ]);

        return $bridge->fresh();
    }

    /**
     * Mark a channel bridge as verified (webhook confirmed).
     */
    public function markVerified(OmniChannelBridge $bridge): OmniChannelBridge
    {
        $bridge->update(['verified_at' => now()]);
        return $bridge->fresh();
    }

    /**
     * Retrieve active bridges for a company, optionally filtered by channel type.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, OmniChannelBridge>
     */
    public function activeBridges(int $companyId, ?string $channelType = null)
    {
        return OmniChannelBridge::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->when($channelType, fn ($q) => $q->where('channel_type', $channelType))
            ->with('agent')
            ->get();
    }
}
