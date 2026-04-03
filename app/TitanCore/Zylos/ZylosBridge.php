<?php

namespace App\TitanCore\Zylos;

use Illuminate\Support\Facades\DB;

/**
 * ZylosBridge – gateway to the Zylos skill execution runtime.
 *
 * This class provides the host-side interface for managing skills registered
 * with the Zylos process manager (PM2 or equivalent). It surfaces skill
 * health, execution state, and control commands to the Titan Core admin panel.
 */
class ZylosBridge
{
    /**
     * Return a snapshot of all registered skill statuses.
     *
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $registered = $this->registeredSkills();
        $running    = [];
        $failed     = [];

        $enriched = [];
        foreach ($registered as $skill) {
            $name = $skill['name'] ?? 'unknown';
            try {
                $last = DB::table('tz_skill_events')
                    ->where('skill_name', $name)
                    ->orderByDesc('created_at')
                    ->first();

                $state = $last ? $last->event_type : 'unknown';

                if ($state === 'skill.failed') {
                    $failed[] = $name;
                } elseif ($state === 'skill.started') {
                    $running[] = $name;
                }

                $skill['last_event']     = $state;
                $skill['last_heartbeat'] = $last->created_at ?? null;
                $skill['last_payload']   = $last ? json_decode($last->payload ?? '{}', true) : null;
            } catch (\Throwable) {
                $skill['last_event']     = 'unknown';
                $skill['last_heartbeat'] = null;
                $skill['last_payload']   = null;
            }

            $enriched[] = $skill;
        }

        return [
            'status'     => 'reachable',
            'registered' => $enriched,
            'running'    => $running,
            'failed'     => $failed,
        ];
    }

    /**
     * Restart a named skill process.
     *
     * @return array<string, mixed>
     */
    public function restart(string $skill): array
    {
        $this->recordEvent($skill, 'skill.restarted', []);

        return ['ok' => true, 'skill' => $skill, 'action' => 'restart_queued'];
    }

    /**
     * Disable a named skill process.
     *
     * @return array<string, mixed>
     */
    public function disable(string $skill): array
    {
        $this->recordEvent($skill, 'skill.disabled', []);

        return ['ok' => true, 'skill' => $skill, 'action' => 'disabled'];
    }

    /**
     * Return list of registered skills from config or DB.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function registeredSkills(): array
    {
        $configured = config('titan_core.skills.registered', []);

        if (! empty($configured)) {
            return array_values($configured);
        }

        try {
            return DB::table('tz_skill_registry')
                ->get()
                ->map(fn ($r) => (array) $r)
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Record a skill lifecycle event.
     */
    protected function recordEvent(string $skill, string $event, array $payload): void
    {
        try {
            DB::table('tz_skill_events')->insert([
                'skill_name'  => $skill,
                'event_type'  => $event,
                'payload'     => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'created_at'  => now(),
            ]);
        } catch (\Throwable) {
            // Non-fatal: table may not exist yet
        }
    }
}
