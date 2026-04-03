<?php

namespace Modules\TitanHello\Services\Routing;

use Carbon\Carbon;
use Modules\TitanHello\Models\InboundNumber;
use Modules\TitanHello\Models\IvrMenu;
use Modules\TitanHello\Models\RingGroup;
use Modules\TitanHello\Services\TwiML\TwiMLBuilder;

class InboundRoutingService
{

    public function resolveCompanyIdByToNumber(string $toNumber): ?int
    {
        $cfg = InboundNumber::query()
            ->where('phone_number', $toNumber)
            ->where('enabled', true)
            ->first();

        return $cfg ? (int) $cfg->company_id : null;
    }

    public function buildInboundResponse(int $companyId, string $toNumber, string $actionUrlBase): string
    {
        $cfg = InboundNumber::query()
            ->where('company_id', $companyId)
            ->where('phone_number', $toNumber)
            ->where('enabled', true)
            ->first();

        // Default fallback: ring nothing, say message.
        if (!$cfg) {
            return (new TwiMLBuilder())
                ->say("Thanks for calling. This number isn't configured yet.")
                ->hangup()
                ->build();
        }

        // Business hours gating (simple; can be upgraded to real hours table later)
        if ($cfg->business_hours_only && !$this->isBusinessHoursFor($cfg)) {
            return $this->renderAfterHours($cfg, $companyId, $actionUrlBase);
        }

        return $this->renderMode($cfg->mode, $cfg->target_id, $companyId, $actionUrlBase);
    }

    public function buildIvrSelectionResponse(int $companyId, int $menuId, string $digit, string $actionUrlBase): string
    {
        $menu = IvrMenu::query()->where('company_id', $companyId)->where('enabled', true)->find($menuId);
        if (!$menu) {
            return (new TwiMLBuilder())->say("Sorry, that menu is unavailable.")->hangup()->build();
        }

        $opt = $menu->options()->where('enabled', true)->where('dtmf', $digit)->first();
        if (!$opt) {
            // Repeat menu
            return $this->renderIvrMenu($menu, $actionUrlBase);
        }

        return $this->renderMode($opt->action_type, $opt->action_target_id, $companyId, $actionUrlBase);
    }

    protected function renderAfterHours(InboundNumber $cfg, int $companyId, string $actionUrlBase): string
    {
        $mode = $cfg->after_hours_mode ?: 'hangup';
        return $this->renderMode($mode, $cfg->after_hours_target_id, $companyId, $actionUrlBase, true);
    }

    protected function renderMode(?string $mode, ?int $targetId, int $companyId, string $actionUrlBase, bool $afterHours = false): string
    {
        $mode = $mode ?: 'hangup';

        if ($mode === 'ring_group' && $targetId) {
            $group = RingGroup::query()->where('company_id', $companyId)->where('enabled', true)->find($targetId);
            if (!$group) {
                return (new TwiMLBuilder())->say("Sorry, we can't connect your call right now.")->hangup()->build();
            }
            return $this->renderRingGroup($group, $afterHours);
        }

        if ($mode === 'ivr' && $targetId) {
            $menu = IvrMenu::query()->where('company_id', $companyId)->where('enabled', true)->find($targetId);
            if (!$menu) {
                return (new TwiMLBuilder())->say("Sorry, our menu is unavailable.")->hangup()->build();
            }
            return $this->renderIvrMenu($menu, $actionUrlBase);
        }

        if ($mode === 'voicemail') {
            $recordUrl = rtrim($actionUrlBase, '/') . '/recording';
            return (new TwiMLBuilder())
                ->say("Please leave a message after the tone.")
                ->record($recordUrl, [
                    'method' => 'POST',
                    'timeout' => 5,
                    'maxLength' => 120,
                    'playBeep' => 'true',
                    // Twilio will POST RecordingUrl/RecordingSid to this callback
                    'recordingStatusCallback' => $recordUrl,
                    'recordingStatusCallbackMethod' => 'POST',
                ])
                ->say("Thank you. Goodbye.")
                ->hangup()
                ->build();
        }

        return (new TwiMLBuilder())->say("Thanks for calling. Goodbye.")->hangup()->build();
    }

    protected function renderRingGroup(RingGroup $group, bool $afterHours = false): string
    {
        $numbers = $group->members()
            ->where('enabled', true)
            ->orderBy('priority')
            ->pluck('phone_number')
            ->toArray();

        if (count($numbers) === 0) {
            return (new TwiMLBuilder())->say("Sorry, nobody is available right now.")->hangup()->build();
        }

        $builder = new TwiMLBuilder();
        if ($afterHours) {
            $builder->say("You've reached us after hours. We'll try to connect you.");
        }
        return $builder->dial($numbers, (int) $group->timeout_seconds)->build();
    }

    protected function renderIvrMenu(IvrMenu $menu, string $actionUrlBase): string
    {
        $actionUrl = rtrim($actionUrlBase, '/') . "/ivr/{$menu->id}/select";

        $builder = new TwiMLBuilder();
        if ($menu->greeting_text) {
            $builder->say($menu->greeting_text);
        } else {
            $builder->say("Press 1 for service. Press 2 for quotes.");
        }

        $builder->gather($actionUrl, (int) $menu->timeout_seconds, 1)->pause(1)->endGather();

        // If no input, repeat once then hang up.
        $builder->say("We didn't get your selection.")->hangup();
        return $builder->build();
    }

    

    protected function isBusinessHoursFor(InboundNumber $cfg): bool
    {
        // If a per-number schedule exists, prefer it.
        // Format: {mon:[['08:00','18:00']], tue:[...], ...} in app timezone.
        $sched = (array)($cfg->business_hours_json ?? []);
        if (!$sched) {
            return $this->isBusinessHours();
        }

        $now = Carbon::now();
        $dayKey = strtolower($now->format('D')); // mon,tue,wed,thu,fri,sat,sun
        $windows = $sched[$dayKey] ?? [];
        if (!is_array($windows) || count($windows) === 0) {
            return false;
        }

        $t = $now->format('H:i');
        for ($i=0; $i<count($windows); $i++) {
            $w = $windows[$i];
            if (!is_array($w) || count($w) < 2) continue;
            $start = (string)$w[0];
            $end = (string)$w[1];
            if ($t >= $start && $t < $end) {
                return true;
            }
        }
        return false;
    }


    protected function isBusinessHours(): bool
    {
        // Basic default: Mon-Fri 8am-6pm local app timezone
        $now = Carbon::now();
        if (in_array($now->dayOfWeekIso, [6,7], true)) {
            return false;
        }
        $h = (int)$now->format('H');
        return $h >= 8 && $h < 18;
    }
}
