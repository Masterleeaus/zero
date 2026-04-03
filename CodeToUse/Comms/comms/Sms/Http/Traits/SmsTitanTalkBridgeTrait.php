<?php

namespace Modules\Sms\Http\Traits;

trait SmsTitanTalkBridgeTrait
{
    /**
     * Log an outgoing SMS into Titan Talk unified inbox (if installed).
     *
     * @param  mixed       $notifiable
     * @param  string|null $text
     * @return void
     */
    protected function logToTitanTalkSms($notifiable, ?string $text = null): void
    {
        try {
            if (! class_exists(\Modules\AIConverse\Models\Conversation::class) ||
                ! class_exists(\Modules\AIConverse\Models\Message::class)) {
                return;
            }

            $phone = null;

            if (isset($notifiable->mobile)) {
                $code = $notifiable->country_phonecode ?? '';
                $phone = $code . $notifiable->mobile;
            } elseif (method_exists($notifiable, 'routeNotificationFor')) {
                $route = $notifiable->routeNotificationFor('vonage') ?? $notifiable->routeNotificationFor('twilio');
                if ($route) {
                    $phone = $route;
                }
            }

            if (! $phone) {
                return;
            }

            $conversation = \Modules\AIConverse\Models\Conversation::firstOrCreate(
                ['channel' => 'sms', 'external_ref' => $phone],
                ['context' => []]
            );

            if ($text === null || $text === '') {
                return;
            }

            \Modules\AIConverse\Models\Message::create([
                'tenant_id'       => $conversation->tenant_id,
                'conversation_id' => $conversation->id,
                'sender'          => 'system',
                'text'            => $text,
                'meta'            => [],
            ]);
        } catch (\Throwable $e) {
            // Titan Talk might not be installed or migrations not run; fail silently.
        }
    }
}
