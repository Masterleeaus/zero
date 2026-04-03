<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Security\BlacklistEmail;
use App\Models\Security\BlacklistIp;
use App\Models\Security\CyberSecurityConfig;
use App\Models\Security\LoginExpiry;
use App\Models\Security\SecurityAuditEvent;
use App\Models\User;
use App\Services\Security\CyberSecurityConfigService;
use App\Services\Security\SecurityAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityDomainTest extends TestCase
{
    use RefreshDatabase;

    // ── Model / schema tests ───────────────────────────────────────────────

    public function test_cyber_security_config_singleton_is_created_on_first_access(): void
    {
        $config = CyberSecurityConfig::singleton();

        $this->assertInstanceOf(CyberSecurityConfig::class, $config);
        $this->assertEquals(3, $config->max_retries);
        $this->assertFalse($config->unique_session);
        $this->assertFalse($config->ip_check);
    }

    public function test_singleton_returns_same_row_on_repeated_calls(): void
    {
        $first  = CyberSecurityConfig::singleton();
        $second = CyberSecurityConfig::singleton();

        $this->assertEquals($first->id, $second->id);
    }

    public function test_blacklist_ip_can_be_stored_and_retrieved(): void
    {
        BlacklistIp::create(['ip_address' => '192.168.1.99']);

        $this->assertDatabaseHas('blacklist_ips', ['ip_address' => '192.168.1.99']);
    }

    public function test_blacklist_email_can_be_stored_and_retrieved(): void
    {
        BlacklistEmail::create(['email' => 'spam@evil.com']);

        $this->assertDatabaseHas('blacklist_emails', ['email' => 'spam@evil.com']);
    }

    public function test_login_expiry_links_to_user(): void
    {
        $user = User::factory()->create();

        $expiry = LoginExpiry::create([
            'user_id'     => $user->id,
            'expiry_date' => now()->subDay()->toDateString(),
        ]);

        $this->assertEquals($user->id, $expiry->user->id);
    }

    // ── SecurityAuditService tests ─────────────────────────────────────────

    public function test_audit_service_records_event_without_tenant(): void
    {
        $service = app(SecurityAuditService::class);

        $event = $service->record(
            SecurityAuditEvent::TYPE_IP_BLOCKED,
            ['ip' => '1.2.3.4'],
            '1.2.3.4',
        );

        $this->assertInstanceOf(SecurityAuditEvent::class, $event);
        $this->assertEquals(SecurityAuditEvent::TYPE_IP_BLOCKED, $event->event_type);
        $this->assertEquals('1.2.3.4', $event->ip_address);
    }

    public function test_audit_service_records_event_with_tenant(): void
    {
        $service = app(SecurityAuditService::class);

        $service->record(
            SecurityAuditEvent::TYPE_LOGIN_LOCKOUT,
            ['email' => 'test@example.com'],
            '10.0.0.1',
            'test@example.com',
            null,
            42,
        );

        $this->assertDatabaseHas('security_audit_events', [
            'company_id' => 42,
            'event_type' => SecurityAuditEvent::TYPE_LOGIN_LOCKOUT,
            'email'      => 'test@example.com',
        ]);
    }

    public function test_audit_service_scopes_events_to_company(): void
    {
        $service = app(SecurityAuditService::class);

        $service->record(SecurityAuditEvent::TYPE_SESSION_REVOKED, [], null, null, null, 10);
        $service->record(SecurityAuditEvent::TYPE_SESSION_REVOKED, [], null, null, null, 10);
        $service->record(SecurityAuditEvent::TYPE_SESSION_REVOKED, [], null, null, null, 20);

        $this->assertCount(2, $service->recentForCompany(10));
        $this->assertCount(1, $service->recentForCompany(20));
    }

    // ── CyberSecurityConfigService tests ──────────────────────────────────

    public function test_config_service_updates_login_protection(): void
    {
        $service = app(CyberSecurityConfigService::class);

        $config = $service->updateLoginProtection([
            'max_retries'  => 5,
            'lockout_time' => 10,
            'email'        => 'security@example.com',
        ]);

        $this->assertEquals(5, $config->max_retries);
        $this->assertEquals(10, $config->lockout_time);
        $this->assertEquals('security@example.com', $config->email);
    }

    public function test_config_service_updates_session_policy(): void
    {
        $service = app(CyberSecurityConfigService::class);

        $config = $service->updateSessionPolicy(true);

        $this->assertTrue($config->unique_session);
    }

    public function test_config_service_detects_blacklisted_ip(): void
    {
        BlacklistIp::create(['ip_address' => '203.0.113.5']);

        $service = app(CyberSecurityConfigService::class);

        $this->assertTrue($service->isIpBlacklisted('203.0.113.5'));
        $this->assertFalse($service->isIpBlacklisted('8.8.8.8'));
    }

    public function test_config_service_detects_blacklisted_email(): void
    {
        BlacklistEmail::create(['email' => 'bad@spam.net']);

        $service = app(CyberSecurityConfigService::class);

        $this->assertTrue($service->isEmailBlacklisted('bad@spam.net'));
        $this->assertFalse($service->isEmailBlacklisted('good@example.com'));
    }

    public function test_config_service_detects_blacklisted_domain(): void
    {
        BlacklistEmail::create(['email' => '@blockedomain.com']);

        $service = app(CyberSecurityConfigService::class);

        $this->assertTrue($service->isEmailBlacklisted('anyone@blockedomain.com'));
        $this->assertFalse($service->isEmailBlacklisted('anyone@safe.com'));
    }

    // ── Middleware: BlackListIpMiddleware ──────────────────────────────────

    public function test_blacklist_ip_middleware_blocks_known_ip(): void
    {
        BlacklistIp::create(['ip_address' => '192.0.2.1']);

        $user = User::factory()->create(['company_id' => 5]);
        $this->actingAs($user);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.1'])
            ->get(route('dashboard.security.settings.index'));

        $response->assertStatus(403);
    }

    public function test_blacklist_ip_middleware_allows_clean_ip(): void
    {
        $user = User::factory()->create(['company_id' => 5, 'is_superadmin' => true]);
        $this->actingAs($user);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get(route('dashboard.security.settings.index'));

        // Not 403 from IP block (may be 200 or other auth-related code)
        $this->assertNotEquals(403, $response->status());
    }

    // ── Middleware: LoginExpiryMiddleware ──────────────────────────────────

    public function test_login_expiry_middleware_logs_out_expired_user(): void
    {
        $user = User::factory()->create(['company_id' => 5]);

        LoginExpiry::create([
            'user_id'     => $user->id,
            'expiry_date' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->withMiddleware(\App\Http\Middleware\Security\LoginExpiryMiddleware::class)
            ->get('/dashboard');

        // User should be redirected to login because their session expired
        $response->assertRedirect();
        $this->assertGuest();
    }

    // ── Route registration ─────────────────────────────────────────────────

    public function test_security_routes_are_registered(): void
    {
        $routes = [
            'dashboard.security.settings.index',
            'dashboard.security.settings.login-protection',
            'dashboard.security.settings.session-policy',
            'dashboard.security.audit.index',
            'dashboard.security.blacklist.ips.index',
            'dashboard.security.blacklist.ips.store',
            'dashboard.security.blacklist.emails.index',
            'dashboard.security.blacklist.emails.store',
        ];

        foreach ($routes as $name) {
            $this->assertTrue(
                \Illuminate\Support\Facades\Route::has($name),
                "Route [{$name}] is not registered.",
            );
        }
    }

    // ── Tenant isolation ───────────────────────────────────────────────────

    public function test_security_audit_events_are_tenant_isolated(): void
    {
        $userA = User::factory()->create(['company_id' => 100]);
        $userB = User::factory()->create(['company_id' => 200]);

        // Record event for company 100
        $this->actingAs($userA);
        $serviceA = app(SecurityAuditService::class);
        $serviceA->record(SecurityAuditEvent::TYPE_POLICY_DENIED, ['reason' => 'test']);

        // Record event for company 200
        $this->actingAs($userB);
        $serviceB = app(SecurityAuditService::class);
        $serviceB->record(SecurityAuditEvent::TYPE_POLICY_DENIED, ['reason' => 'test']);

        $eventsA = $serviceA->recentForCompany(100);
        $eventsB = $serviceB->recentForCompany(200);

        $this->assertCount(1, $eventsA);
        $this->assertCount(1, $eventsB);

        $this->assertEquals(100, $eventsA->first()->company_id);
        $this->assertEquals(200, $eventsB->first()->company_id);
    }
}
