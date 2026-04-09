<?php

declare(strict_types=1);

namespace Tests\Feature\Omni;

use App\Contracts\Omni\DeliveryStatusContract;
use App\Contracts\Omni\InboundDriverContract;
use App\Contracts\Omni\OmniDriverContract;
use App\Contracts\Omni\OutboundDriverContract;
use App\Contracts\Omni\ProviderAuthContract;
use App\Services\Drivers\AbstractOmniDriver;
use App\Services\Drivers\EmailDriver;
use App\Services\Drivers\SmsDriver;
use App\Services\Drivers\TelegramDriver;
use App\Services\Drivers\VoiceDriver;
use App\Services\Drivers\WebchatDriver;
use App\Services\Drivers\WhatsAppMetaDriver;
use App\Services\Drivers\WhatsAppTwilioDriver;
use App\Services\Omni\OmniDriverRegistry;
use ReflectionClass;
use Tests\TestCase;

/**
 * TITAN OMNI — Pass 04 Test
 *
 * Validates:
 *   - All 5 contract interfaces exist with correct method signatures
 *   - AbstractOmniDriver base class behaviour
 *   - All 7 drivers instantiate and return correct channel types
 *   - WebchatDriver is always configured
 *   - SmsDriver isConfigured() responds correctly to config presence
 *   - OmniDriverRegistry: register, has, get, all, allOutbound, allInbound, configured
 *   - config/titan_omni.php has 'channels' and 'drivers' keys
 */
class OmniPass04Test extends TestCase
{
    // ── 1. Contract interface existence and method signatures ─────────────────

    public function test_omni_driver_contract_interface_exists(): void
    {
        $this->assertTrue(interface_exists(OmniDriverContract::class));

        $ref = new ReflectionClass(OmniDriverContract::class);
        $this->assertTrue($ref->hasMethod('getChannelType'));
        $this->assertTrue($ref->hasMethod('isConfigured'));
        $this->assertTrue($ref->hasMethod('ping'));
    }

    public function test_outbound_driver_contract_extends_base(): void
    {
        $this->assertTrue(interface_exists(OutboundDriverContract::class));

        $ref = new ReflectionClass(OutboundDriverContract::class);
        $this->assertTrue($ref->isSubclassOf(OmniDriverContract::class));
        $this->assertTrue($ref->hasMethod('send'));
        $this->assertTrue($ref->hasMethod('sendBatch'));
    }

    public function test_inbound_driver_contract_extends_base(): void
    {
        $this->assertTrue(interface_exists(InboundDriverContract::class));

        $ref = new ReflectionClass(InboundDriverContract::class);
        $this->assertTrue($ref->isSubclassOf(OmniDriverContract::class));
        $this->assertTrue($ref->hasMethod('verify'));
        $this->assertTrue($ref->hasMethod('normalize'));
    }

    public function test_delivery_status_contract_extends_base(): void
    {
        $this->assertTrue(interface_exists(DeliveryStatusContract::class));

        $ref = new ReflectionClass(DeliveryStatusContract::class);
        $this->assertTrue($ref->isSubclassOf(OmniDriverContract::class));
        $this->assertTrue($ref->hasMethod('parseStatus'));
    }

    public function test_provider_auth_contract_extends_base(): void
    {
        $this->assertTrue(interface_exists(ProviderAuthContract::class));

        $ref = new ReflectionClass(ProviderAuthContract::class);
        $this->assertTrue($ref->isSubclassOf(OmniDriverContract::class));
        $this->assertTrue($ref->hasMethod('authenticate'));
        $this->assertTrue($ref->hasMethod('refreshCredentials'));
        $this->assertTrue($ref->hasMethod('getCredentials'));
    }

    // ── 2. AbstractOmniDriver base class ──────────────────────────────────────

    public function test_abstract_driver_is_abstract(): void
    {
        $ref = new ReflectionClass(AbstractOmniDriver::class);
        $this->assertTrue($ref->isAbstract());
        $this->assertTrue($ref->implementsInterface(OmniDriverContract::class));
    }

    public function test_abstract_driver_ping_delegates_to_is_configured(): void
    {
        $driver = new WebchatDriver(); // concrete, no required keys
        $this->assertTrue($driver->ping());
    }

    // ── 3. All 7 drivers instantiate ─────────────────────────────────────────

    public function test_sms_driver_instantiates(): void
    {
        $driver = new SmsDriver();
        $this->assertInstanceOf(SmsDriver::class, $driver);
        $this->assertInstanceOf(OmniDriverContract::class, $driver);
    }

    public function test_email_driver_instantiates(): void
    {
        $driver = new EmailDriver();
        $this->assertInstanceOf(EmailDriver::class, $driver);
        $this->assertInstanceOf(OmniDriverContract::class, $driver);
    }

    public function test_whatsapp_meta_driver_instantiates(): void
    {
        $driver = new WhatsAppMetaDriver();
        $this->assertInstanceOf(WhatsAppMetaDriver::class, $driver);
        $this->assertInstanceOf(OmniDriverContract::class, $driver);
    }

    public function test_whatsapp_twilio_driver_instantiates(): void
    {
        $driver = new WhatsAppTwilioDriver();
        $this->assertInstanceOf(WhatsAppTwilioDriver::class, $driver);
        $this->assertInstanceOf(OmniDriverContract::class, $driver);
    }

    public function test_telegram_driver_instantiates(): void
    {
        $driver = new TelegramDriver();
        $this->assertInstanceOf(TelegramDriver::class, $driver);
        $this->assertInstanceOf(OmniDriverContract::class, $driver);
    }

    public function test_webchat_driver_instantiates(): void
    {
        $driver = new WebchatDriver();
        $this->assertInstanceOf(WebchatDriver::class, $driver);
        $this->assertInstanceOf(OmniDriverContract::class, $driver);
    }

    public function test_voice_driver_instantiates(): void
    {
        $driver = new VoiceDriver();
        $this->assertInstanceOf(VoiceDriver::class, $driver);
        $this->assertInstanceOf(OmniDriverContract::class, $driver);
    }

    // ── 4. Driver channel type strings ───────────────────────────────────────

    public function test_driver_channel_types(): void
    {
        $this->assertSame('sms', (new SmsDriver())->getChannelType());
        $this->assertSame('email', (new EmailDriver())->getChannelType());
        $this->assertSame('whatsapp_meta', (new WhatsAppMetaDriver())->getChannelType());
        $this->assertSame('whatsapp_twilio', (new WhatsAppTwilioDriver())->getChannelType());
        $this->assertSame('telegram', (new TelegramDriver())->getChannelType());
        $this->assertSame('webchat', (new WebchatDriver())->getChannelType());
        $this->assertSame('voice', (new VoiceDriver())->getChannelType());
    }

    // ── 5. WebchatDriver is always configured ─────────────────────────────────

    public function test_webchat_always_configured(): void
    {
        $this->assertTrue((new WebchatDriver())->isConfigured());
        $this->assertTrue((new WebchatDriver([]))->isConfigured());
    }

    // ── 6. SmsDriver isConfigured() responds to config ───────────────────────

    public function test_sms_driver_not_configured_when_empty(): void
    {
        $this->assertFalse((new SmsDriver())->isConfigured());
        $this->assertFalse((new SmsDriver([]))->isConfigured());
        $this->assertFalse((new SmsDriver(['sid' => 'x']))->isConfigured());
    }

    public function test_sms_driver_configured_when_all_keys_present(): void
    {
        $driver = new SmsDriver([
            'sid'   => 'ACtest123',
            'token' => 'authtoken',
            'from'  => '+15550001234',
        ]);

        $this->assertTrue($driver->isConfigured());
    }

    // ── 7. OmniDriverRegistry ─────────────────────────────────────────────────

    private function makeRegistry(): OmniDriverRegistry
    {
        $registry = new OmniDriverRegistry();
        $registry->register(new SmsDriver(['sid' => 'x', 'token' => 'y', 'from' => 'z']));
        $registry->register(new EmailDriver(['from_address' => 'a@b.com', 'from_name' => 'A']));
        $registry->register(new WebchatDriver());
        $registry->register(new TelegramDriver());

        return $registry;
    }

    public function test_registry_has_and_get(): void
    {
        $registry = $this->makeRegistry();

        $this->assertTrue($registry->has('sms'));
        $this->assertTrue($registry->has('email'));
        $this->assertTrue($registry->has('webchat'));
        $this->assertFalse($registry->has('nonexistent'));

        $this->assertInstanceOf(SmsDriver::class, $registry->get('sms'));
        $this->assertInstanceOf(EmailDriver::class, $registry->get('email'));
    }

    public function test_registry_get_throws_for_unknown_channel(): void
    {
        $registry = new OmniDriverRegistry();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/nonexistent/');

        $registry->get('nonexistent');
    }

    public function test_registry_all_returns_all_drivers(): void
    {
        $registry = $this->makeRegistry();
        $all      = $registry->all();

        $this->assertArrayHasKey('sms', $all);
        $this->assertArrayHasKey('email', $all);
        $this->assertArrayHasKey('webchat', $all);
        $this->assertCount(4, $all);
    }

    public function test_registry_all_outbound(): void
    {
        $registry = $this->makeRegistry();
        $outbound = $registry->allOutbound();

        foreach ($outbound as $driver) {
            $this->assertInstanceOf(OutboundDriverContract::class, $driver);
        }

        // All 4 registered drivers implement OutboundDriverContract
        $this->assertArrayHasKey('sms', $outbound);
        $this->assertArrayHasKey('email', $outbound);
        $this->assertArrayHasKey('webchat', $outbound);
    }

    public function test_registry_all_inbound(): void
    {
        $registry = $this->makeRegistry();
        $inbound  = $registry->allInbound();

        foreach ($inbound as $driver) {
            $this->assertInstanceOf(InboundDriverContract::class, $driver);
        }

        // SmsDriver, WebchatDriver, TelegramDriver implement InboundDriverContract
        $this->assertArrayHasKey('sms', $inbound);
        $this->assertArrayHasKey('webchat', $inbound);
        $this->assertArrayHasKey('telegram', $inbound);

        // EmailDriver does NOT implement InboundDriverContract
        $this->assertArrayNotHasKey('email', $inbound);
    }

    public function test_registry_configured_returns_only_configured_drivers(): void
    {
        $registry = $this->makeRegistry();
        $configured = $registry->configured();

        // SmsDriver has full config, EmailDriver has full config, WebchatDriver always true
        $this->assertArrayHasKey('sms', $configured);
        $this->assertArrayHasKey('email', $configured);
        $this->assertArrayHasKey('webchat', $configured);

        // TelegramDriver has no config so is NOT configured
        $this->assertArrayNotHasKey('telegram', $configured);
    }

    // ── 8. config/titan_omni.php has 'channels' and 'drivers' keys ───────────

    public function test_config_has_channels_key(): void
    {
        $config = config('titan_omni');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('channels', $config);

        $channels = $config['channels'];
        $this->assertArrayHasKey('sms', $channels);
        $this->assertArrayHasKey('email', $channels);
        $this->assertArrayHasKey('whatsapp_meta', $channels);
        $this->assertArrayHasKey('whatsapp_twilio', $channels);
        $this->assertArrayHasKey('telegram', $channels);
        $this->assertArrayHasKey('webchat', $channels);
        $this->assertArrayHasKey('voice', $channels);
    }

    public function test_config_has_drivers_key(): void
    {
        $config = config('titan_omni');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('drivers', $config);

        $drivers = $config['drivers'];
        $this->assertArrayHasKey('sms', $drivers);
        $this->assertArrayHasKey('email', $drivers);
        $this->assertArrayHasKey('whatsapp_meta', $drivers);
        $this->assertArrayHasKey('whatsapp_twilio', $drivers);
        $this->assertArrayHasKey('telegram', $drivers);
        $this->assertArrayHasKey('webchat', $drivers);
        $this->assertArrayHasKey('voice', $drivers);
    }

    public function test_config_channel_driver_keys_match(): void
    {
        $channels = config('titan_omni.channels');

        foreach ($channels as $key => $channel) {
            $this->assertArrayHasKey('driver', $channel, "Channel '{$key}' is missing 'driver' key");
            $this->assertSame($key, $channel['driver'], "Channel '{$key}' driver key mismatch");
        }
    }

    // ── 9. Driver contract implementation matrix ──────────────────────────────

    public function test_sms_driver_implements_all_three_contracts(): void
    {
        $driver = new SmsDriver();
        $this->assertInstanceOf(OutboundDriverContract::class, $driver);
        $this->assertInstanceOf(InboundDriverContract::class, $driver);
        $this->assertInstanceOf(DeliveryStatusContract::class, $driver);
    }

    public function test_email_driver_implements_only_outbound(): void
    {
        $driver = new EmailDriver();
        $this->assertInstanceOf(OutboundDriverContract::class, $driver);
        $this->assertNotInstanceOf(InboundDriverContract::class, $driver);
        $this->assertNotInstanceOf(DeliveryStatusContract::class, $driver);
    }

    public function test_whatsapp_meta_implements_all_three_contracts(): void
    {
        $driver = new WhatsAppMetaDriver();
        $this->assertInstanceOf(OutboundDriverContract::class, $driver);
        $this->assertInstanceOf(InboundDriverContract::class, $driver);
        $this->assertInstanceOf(DeliveryStatusContract::class, $driver);
    }

    public function test_telegram_driver_implements_outbound_and_inbound(): void
    {
        $driver = new TelegramDriver();
        $this->assertInstanceOf(OutboundDriverContract::class, $driver);
        $this->assertInstanceOf(InboundDriverContract::class, $driver);
        $this->assertNotInstanceOf(DeliveryStatusContract::class, $driver);
    }

    public function test_webchat_driver_implements_outbound_and_inbound(): void
    {
        $driver = new WebchatDriver();
        $this->assertInstanceOf(OutboundDriverContract::class, $driver);
        $this->assertInstanceOf(InboundDriverContract::class, $driver);
    }

    public function test_voice_driver_implements_outbound_and_inbound(): void
    {
        $driver = new VoiceDriver();
        $this->assertInstanceOf(OutboundDriverContract::class, $driver);
        $this->assertInstanceOf(InboundDriverContract::class, $driver);
    }

    // ── 10. Driver send/normalize smoke tests (no real provider calls) ────────

    public function test_sms_driver_send_returns_expected_keys(): void
    {
        $driver = new SmsDriver(['sid' => 'x', 'token' => 'y', 'from' => '+15550000000']);
        $result = $driver->send(['to' => '+15559999999', 'body' => 'Hello']);

        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('provider_message_id', $result);
        $this->assertArrayHasKey('raw', $result);
    }

    public function test_webchat_driver_send_returns_delivered(): void
    {
        $driver = new WebchatDriver();
        $result = $driver->send(['session_id' => 'sess123', 'body' => 'Hi']);

        $this->assertSame('delivered', $result['status']);
    }

    public function test_sms_driver_normalize_extracts_fields(): void
    {
        $driver = new SmsDriver(['sid' => 'x', 'token' => 'y', 'from' => '+1']);
        $raw    = http_build_query([
            'From'          => '+15551234567',
            'Body'          => 'Test message',
            'MessageSid'    => 'SM123',
        ]);

        $normalized = $driver->normalize([], $raw);

        $this->assertSame('+15551234567', $normalized['from']);
        $this->assertSame('Test message', $normalized['body']);
        $this->assertSame('SM123', $normalized['provider_message_id']);
    }

    public function test_whatsapp_twilio_normalize_strips_prefix(): void
    {
        $driver = new WhatsAppTwilioDriver(['sid' => 'x', 'token' => 'y', 'from' => '+1']);
        $raw    = http_build_query([
            'From'       => 'whatsapp:+15551234567',
            'Body'       => 'Hello',
            'MessageSid' => 'SM456',
        ]);

        $normalized = $driver->normalize([], $raw);

        $this->assertSame('+15551234567', $normalized['from']);
        $this->assertSame('whatsapp_twilio', $normalized['channel']);
    }

    public function test_webchat_driver_normalize_extracts_fields(): void
    {
        $driver = new WebchatDriver();
        $raw    = json_encode([
            'session_id'    => 'sess-abc',
            'message'       => 'Hello chat',
            'customer_name' => 'Jane Doe',
        ]);

        $normalized = $driver->normalize([], $raw);

        $this->assertSame('sess-abc', $normalized['session_id']);
        $this->assertSame('Hello chat', $normalized['body']);
        $this->assertSame('Jane Doe', $normalized['customer_name']);
    }

    public function test_sms_driver_parse_status(): void
    {
        $driver  = new SmsDriver();
        $payload = ['MessageSid' => 'SM999', 'MessageStatus' => 'delivered', 'Timestamp' => '2024-01-01'];
        $result  = $driver->parseStatus($payload);

        $this->assertSame('SM999', $result['provider_message_id']);
        $this->assertSame('delivered', $result['status']);
    }

    // ── 11. OmniDriverRegistry singleton via service container ───────────────

    public function test_driver_registry_resolves_from_container(): void
    {
        $registry = $this->app->make(OmniDriverRegistry::class);
        $this->assertInstanceOf(OmniDriverRegistry::class, $registry);

        $this->assertTrue($registry->has('sms'));
        $this->assertTrue($registry->has('email'));
        $this->assertTrue($registry->has('whatsapp_meta'));
        $this->assertTrue($registry->has('whatsapp_twilio'));
        $this->assertTrue($registry->has('telegram'));
        $this->assertTrue($registry->has('webchat'));
        $this->assertTrue($registry->has('voice'));
    }
}
