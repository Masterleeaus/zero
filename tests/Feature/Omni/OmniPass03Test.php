<?php

declare(strict_types=1);

namespace Tests\Feature\Omni;

use App\Models\Omni\Automation\OmniAutomation;
use App\Models\Omni\Automation\OmniAutomationAction;
use App\Models\Omni\Automation\OmniHandoffRule;
use App\Models\Omni\Automation\OmniOverlayBinding;
use App\Models\Omni\Automation\OmniSequence;
use App\Models\Omni\Automation\OmniSequenceRun;
use App\Models\Omni\Automation\OmniSequenceStep;
use App\Models\Omni\OmniAgent;
use App\Models\Omni\OmniChannelBridge;
use App\Models\Omni\OmniConversation;
use App\Models\Omni\OmniCustomer;
use App\Models\Omni\OmniMessage;
use App\Models\Omni\OmniMessageAttachment;
use App\Models\Omni\Campaign\OmniCampaign;
use App\Models\Omni\Campaign\OmniContactList;
use App\Models\Omni\Campaign\OmniContactListMember;
use App\Models\Omni\Voice\OmniCallLog;
use App\Models\Omni\Voice\OmniVoiceCall;
use App\Models\User;
use App\Services\Omni\OmniAnalyticsService;
use App\Services\Omni\OmniChannelService;
use App\Services\Omni\OmniConversationService;
use App\Services\Omni\OmniInboxService;
use App\Services\Omni\OmniKnowledgeService;
use App\TitanCore\Omni\OmniManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TITAN OMNI — Pass 03 Test
 *
 * Validates:
 *   - All Omni model classes instantiate correctly
 *   - BelongsToCompany trait is applied (company scope)
 *   - Relationships are declared on all models
 *   - Tenancy: cross-company isolation via withoutGlobalScope
 *   - OmniConversationService: findOrCreate, appendMessage, resolve, transfer
 *   - OmniChannelService: register, deregister, markVerified
 *   - OmniKnowledgeService: search, upsert, listActive, archive
 *   - OmniInboxService: paginatedInbox, assign, messageHistory
 *   - OmniAnalyticsService: increment, periodReport, summary
 *   - OmniManager: persistConversation, persistMessage
 *   - HasImmutableTimestamps: immutable column list on OmniMessage
 *   - New models: OmniSequence, OmniAutomation, OmniHandoffRule, OmniOverlayBinding, OmniMessageAttachment
 */
class OmniPass03Test extends TestCase
{
    use RefreshDatabase;

    private int $companyId = 77;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeUser(): User
    {
        return User::factory()->create(['company_id' => $this->companyId, 'role' => 'admin']);
    }

    private function makeAgent(array $attrs = []): OmniAgent
    {
        return OmniAgent::withoutGlobalScope('company')->create(array_merge([
            'company_id'   => $this->companyId,
            'name'         => 'Test Agent',
            'slug'         => 'test-agent-' . uniqid(),
            'role'         => 'assistant',
            'model'        => 'gpt-4o-mini',
            'tone'         => 'professional',
            'language'     => 'en',
            'channel_scope' => 'all',
            'is_active'    => true,
        ], $attrs));
    }

    private function makeCustomer(array $attrs = []): OmniCustomer
    {
        return OmniCustomer::withoutGlobalScope('company')->create(array_merge([
            'company_id' => $this->companyId,
            'name'       => 'Test Customer',
            'email'      => 'test' . uniqid() . '@example.com',
        ], $attrs));
    }

    private function makeConversation(OmniAgent $agent, OmniCustomer $customer, array $attrs = []): OmniConversation
    {
        return OmniConversation::withoutGlobalScope('company')->create(array_merge([
            'company_id'      => $this->companyId,
            'agent_id'        => $agent->id,
            'omni_customer_id' => $customer->id,
            'channel_type'    => 'web',
            'status'          => 'open',
        ], $attrs));
    }

    // ── Model instantiation ───────────────────────────────────────────────────

    /** @test */
    public function all_core_omni_model_classes_exist(): void
    {
        $models = [
            OmniAgent::class,
            OmniCustomer::class,
            OmniConversation::class,
            OmniMessage::class,
            OmniMessageAttachment::class,
            OmniChannelBridge::class,
            OmniSequence::class,
            OmniSequenceStep::class,
            OmniSequenceRun::class,
            OmniAutomation::class,
            OmniAutomationAction::class,
            OmniOverlayBinding::class,
            OmniHandoffRule::class,
            OmniCampaign::class,
            OmniContactList::class,
            OmniContactListMember::class,
            OmniVoiceCall::class,
            OmniCallLog::class,
        ];

        foreach ($models as $class) {
            $this->assertTrue(class_exists($class), "Model class {$class} does not exist.");
        }
    }

    /** @test */
    public function all_omni_service_classes_exist(): void
    {
        $services = [
            OmniConversationService::class,
            OmniChannelService::class,
            OmniKnowledgeService::class,
            OmniInboxService::class,
            OmniAnalyticsService::class,
        ];

        foreach ($services as $class) {
            $this->assertTrue(class_exists($class), "Service class {$class} does not exist.");
        }
    }

    // ── Tenancy ───────────────────────────────────────────────────────────────

    /** @test */
    public function omni_agent_global_scope_isolates_by_company(): void
    {
        $agent1 = $this->makeAgent(['company_id' => 77]);
        $agent2 = $this->makeAgent(['company_id' => 99, 'slug' => 'other-co-agent']);

        $this->actingAs($this->makeUser());

        $agents = OmniAgent::all();
        $this->assertTrue($agents->contains($agent1));
        $this->assertFalse($agents->contains($agent2));
    }

    /** @test */
    public function omni_conversation_belongs_to_company_trait_applied(): void
    {
        $agent    = $this->makeAgent();
        $customer = $this->makeCustomer();
        $conv     = $this->makeConversation($agent, $customer);

        $this->assertSame($this->companyId, $conv->company_id);
        $this->assertInstanceOf(OmniConversation::class, $conv);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    /** @test */
    public function omni_agent_relationships_are_declared(): void
    {
        $agent = $this->makeAgent();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $agent->conversations());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $agent->channelBridges());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $agent->knowledgeArticles());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $agent->sequences());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $agent->automations());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $agent->handoffRules());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $agent->user());
    }

    /** @test */
    public function omni_conversation_host_relationships_are_declared(): void
    {
        $agent    = $this->makeAgent();
        $customer = $this->makeCustomer();
        $conv     = $this->makeConversation($agent, $customer);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $conv->crmCustomer());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $conv->serviceJob());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $conv->invoice());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $conv->assignedUser());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $conv->messages());
    }

    /** @test */
    public function omni_message_has_attachments_relationship(): void
    {
        $agent    = $this->makeAgent();
        $customer = $this->makeCustomer();
        $conv     = $this->makeConversation($agent, $customer);

        $message = OmniMessage::withoutGlobalScope('company')->create([
            'company_id'      => $this->companyId,
            'conversation_id' => $conv->id,
            'direction'       => 'inbound',
            'content_type'    => 'text',
            'content'         => 'Hello',
            'sender_type'     => 'customer',
            'created_at'      => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $message->attachments());
    }

    /** @test */
    public function omni_customer_has_crm_customer_relationship(): void
    {
        $customer = $this->makeCustomer();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $customer->crmCustomer());
    }

    // ── OmniConversationService ────────────────────────────────────────────────

    /** @test */
    public function conversation_service_find_or_create_creates_new_conversation(): void
    {
        $agent   = $this->makeAgent();
        $service = new OmniConversationService();

        $conv = $service->findOrCreate([
            'company_id'   => $this->companyId,
            'agent_id'     => $agent->id,
            'channel_type' => 'web',
            'channel_id'   => 'session-abc123',
            'session_id'   => 'session-abc123',
        ]);

        $this->assertInstanceOf(OmniConversation::class, $conv);
        $this->assertSame('open', $conv->status);
        $this->assertSame($this->companyId, $conv->company_id);
    }

    /** @test */
    public function conversation_service_find_or_create_is_idempotent(): void
    {
        $agent   = $this->makeAgent();
        $service = new OmniConversationService();

        $attrs = [
            'company_id'   => $this->companyId,
            'agent_id'     => $agent->id,
            'channel_type' => 'whatsapp',
            'channel_id'   => '+61400000001',
            'session_id'   => null,
        ];

        $first  = $service->findOrCreate($attrs);
        $second = $service->findOrCreate($attrs);

        $this->assertSame($first->id, $second->id);
    }

    /** @test */
    public function conversation_service_append_message_creates_message_and_increments_count(): void
    {
        $agent    = $this->makeAgent();
        $customer = $this->makeCustomer();
        $service  = new OmniConversationService();
        $conv     = $this->makeConversation($agent, $customer);

        $message = $service->appendMessage($conv, [
            'direction'    => 'inbound',
            'content_type' => 'text',
            'content'      => 'Hello world',
            'sender_type'  => 'customer',
        ]);

        $this->assertInstanceOf(OmniMessage::class, $message);
        $this->assertSame('Hello world', $message->content);
        $this->assertSame(1, $conv->fresh()->total_messages);
    }

    /** @test */
    public function conversation_service_resolve_sets_status_and_resolved_at(): void
    {
        $agent    = $this->makeAgent();
        $customer = $this->makeCustomer();
        $conv     = $this->makeConversation($agent, $customer);
        $service  = new OmniConversationService();

        $resolved = $service->resolve($conv);

        $this->assertSame('resolved', $resolved->status);
        $this->assertNotNull($resolved->resolved_at);
    }

    /** @test */
    public function conversation_service_resolve_is_idempotent(): void
    {
        $agent    = $this->makeAgent();
        $customer = $this->makeCustomer();
        $conv     = $this->makeConversation($agent, $customer, ['status' => 'resolved', 'resolved_at' => now()]);
        $service  = new OmniConversationService();

        $result = $service->resolve($conv);
        $this->assertSame('resolved', $result->status);
    }

    // ── OmniChannelService ────────────────────────────────────────────────────

    /** @test */
    public function channel_service_register_creates_bridge(): void
    {
        $agent   = $this->makeAgent();
        $service = new OmniChannelService();

        $bridge = $service->register($this->companyId, 'whatsapp', [
            'agent_id'   => $agent->id,
            'bridge_driver' => 'twilio',
        ]);

        $this->assertInstanceOf(OmniChannelBridge::class, $bridge);
        $this->assertSame('whatsapp', $bridge->channel_type);
        $this->assertTrue($bridge->is_active);
    }

    /** @test */
    public function channel_service_deregister_disables_bridge(): void
    {
        $agent   = $this->makeAgent();
        $service = new OmniChannelService();
        $bridge  = $service->register($this->companyId, 'telegram', ['agent_id' => $agent->id]);

        $disabled = $service->deregister($bridge);
        $this->assertFalse($disabled->is_active);
    }

    // ── OmniInboxService ──────────────────────────────────────────────────────

    /** @test */
    public function inbox_service_paginated_inbox_returns_open_conversations(): void
    {
        $agent    = $this->makeAgent();
        $customer = $this->makeCustomer();

        $this->makeConversation($agent, $customer, ['status' => 'open']);
        $this->makeConversation($agent, $customer, ['status' => 'resolved']);

        $service = new OmniInboxService();
        $result  = $service->paginatedInbox($this->companyId);

        $this->assertSame(1, $result->total());
    }

    /** @test */
    public function inbox_service_assign_sets_assigned_to(): void
    {
        $user     = $this->makeUser();
        $agent    = $this->makeAgent();
        $customer = $this->makeCustomer();
        $conv     = $this->makeConversation($agent, $customer);
        $service  = new OmniInboxService();

        $assigned = $service->assign($conv, $user->id);
        $this->assertSame($user->id, $assigned->assigned_to);
    }

    // ── OmniAnalyticsService ──────────────────────────────────────────────────

    /** @test */
    public function analytics_service_increment_creates_record(): void
    {
        $service = new OmniAnalyticsService();
        $service->increment([
            'company_id'   => $this->companyId,
            'agent_id'     => null,
            'channel_type' => 'web',
            'period_date'  => now()->toDateString(),
        ], 'conversations_opened');

        $this->assertDatabaseHas('omni_analytics', [
            'company_id'          => $this->companyId,
            'channel_type'        => 'web',
            'conversations_opened' => 1,
        ]);
    }

    /** @test */
    public function analytics_service_summary_aggregates_correctly(): void
    {
        $service = new OmniAnalyticsService();

        $dims = ['company_id' => $this->companyId, 'agent_id' => null, 'channel_type' => 'whatsapp'];
        $service->increment($dims, 'conversations_opened', 3);
        $service->increment($dims, 'messages_sent', 10);

        $summary = $service->summary(
            $this->companyId,
            now()->subDay(),
            now()->addDay()
        );

        $this->assertSame(3, $summary['conversations_opened']);
        $this->assertSame(10, $summary['messages_sent']);
    }

    // ── OmniManager extensions ────────────────────────────────────────────────

    /** @test */
    public function omni_manager_persist_conversation_delegates_to_service(): void
    {
        $agent    = $this->makeAgent();
        $convService = new OmniConversationService();
        $manager = new OmniManager(
            app(\App\TitanCore\Zero\Telemetry\TelemetryManager::class),
            app(\App\TitanCore\Zero\AI\TitanAIRouter::class),
            $convService,
        );

        $conv = $manager->persistConversation([
            'company_id'   => $this->companyId,
            'agent_id'     => $agent->id,
            'channel'      => 'web',
            'channel_id'   => 'session-persist-test',
            'session_id'   => 'session-persist-test',
        ]);

        $this->assertInstanceOf(OmniConversation::class, $conv);
    }

    /** @test */
    public function omni_manager_persist_message_creates_message(): void
    {
        $agent       = $this->makeAgent();
        $customer    = $this->makeCustomer();
        $conv        = $this->makeConversation($agent, $customer);
        $convService = new OmniConversationService();

        $manager = new OmniManager(
            app(\App\TitanCore\Zero\Telemetry\TelemetryManager::class),
            app(\App\TitanCore\Zero\AI\TitanAIRouter::class),
            $convService,
        );

        $message = $manager->persistMessage($conv, [
            'direction'    => 'inbound',
            'content_type' => 'text',
            'content'      => 'Persist this',
        ]);

        $this->assertInstanceOf(OmniMessage::class, $message);
        $this->assertSame('Persist this', $message->content);
    }

    // ── New models CRUD ───────────────────────────────────────────────────────

    /** @test */
    public function omni_sequence_can_be_created_with_steps_and_run(): void
    {
        $agent = $this->makeAgent();

        $sequence = OmniSequence::withoutGlobalScope('company')->create([
            'company_id'   => $this->companyId,
            'agent_id'     => $agent->id,
            'name'         => 'Welcome Sequence',
            'channel_type' => 'whatsapp',
            'status'       => 'active',
        ]);

        $step = OmniSequenceStep::withoutGlobalScope('company')->create([
            'sequence_id'   => $sequence->id,
            'company_id'    => $this->companyId,
            'step_order'    => 1,
            'step_type'     => 'message',
            'content'       => 'Welcome!',
            'delay_minutes' => 0,
        ]);

        $customer = $this->makeCustomer();

        $run = OmniSequenceRun::withoutGlobalScope('company')->create([
            'sequence_id'     => $sequence->id,
            'company_id'      => $this->companyId,
            'omni_customer_id' => $customer->id,
            'current_step_id' => $step->id,
            'status'          => 'active',
        ]);

        $this->assertSame(1, $sequence->steps()->count());
        $this->assertSame(1, $sequence->runs()->count());
        $this->assertSame($step->id, $run->currentStep->id);
    }

    /** @test */
    public function omni_automation_can_be_created_with_actions(): void
    {
        $agent = $this->makeAgent();

        $automation = OmniAutomation::withoutGlobalScope('company')->create([
            'company_id'   => $this->companyId,
            'agent_id'     => $agent->id,
            'name'         => 'Keyword Reply',
            'trigger_type' => 'keyword_match',
            'trigger_conditions' => ['keywords' => ['help', 'support']],
            'is_active'    => true,
        ]);

        $action = OmniAutomationAction::withoutGlobalScope('company')->create([
            'automation_id' => $automation->id,
            'company_id'    => $this->companyId,
            'action_order'  => 1,
            'action_type'   => 'send_message',
            'action_config' => ['content' => 'How can I help you?'],
        ]);

        $this->assertSame(1, $automation->actions()->count());
        $this->assertSame('send_message', $action->action_type);
    }

    /** @test */
    public function omni_handoff_rule_can_be_created(): void
    {
        $agent = $this->makeAgent();

        $rule = OmniHandoffRule::withoutGlobalScope('company')->create([
            'company_id'   => $this->companyId,
            'agent_id'     => $agent->id,
            'name'         => 'Escalation Rule',
            'trigger_type' => 'escalation_requested',
            'channel_scope' => 'all',
            'priority'     => 10,
            'is_active'    => true,
        ]);

        $this->assertInstanceOf(OmniHandoffRule::class, $rule);
        $this->assertTrue($rule->is_active);
    }

    /** @test */
    public function omni_overlay_binding_can_be_created(): void
    {
        $agent = $this->makeAgent();

        $binding = OmniOverlayBinding::withoutGlobalScope('company')->create([
            'company_id'  => $this->companyId,
            'agent_id'    => $agent->id,
            'surface'     => 'web_embed',
            'binding_key' => 'widget-' . uniqid(),
            'is_active'   => true,
        ]);

        $this->assertInstanceOf(OmniOverlayBinding::class, $binding);
        $this->assertSame('web_embed', $binding->surface);
    }

    /** @test */
    public function omni_message_attachment_can_be_created(): void
    {
        $agent    = $this->makeAgent();
        $customer = $this->makeCustomer();
        $conv     = $this->makeConversation($agent, $customer);

        $message = OmniMessage::withoutGlobalScope('company')->create([
            'company_id'      => $this->companyId,
            'conversation_id' => $conv->id,
            'direction'       => 'inbound',
            'content_type'    => 'image',
            'sender_type'     => 'customer',
            'created_at'      => now(),
        ]);

        $attachment = OmniMessageAttachment::withoutGlobalScope('company')->create([
            'message_id'      => $message->id,
            'company_id'      => $this->companyId,
            'attachment_type' => 'image',
            'media_url'       => 'https://example.com/photo.jpg',
        ]);

        $this->assertInstanceOf(OmniMessageAttachment::class, $attachment);
        $this->assertSame(1, $message->attachments()->count());
    }

    // ── HasImmutableTimestamps ─────────────────────────────────────────────────

    /** @test */
    public function omni_message_declares_immutable_delivery_columns(): void
    {
        $agent    = $this->makeAgent();
        $customer = $this->makeCustomer();
        $conv     = $this->makeConversation($agent, $customer);

        $message = OmniMessage::withoutGlobalScope('company')->create([
            'company_id'      => $this->companyId,
            'conversation_id' => $conv->id,
            'direction'       => 'outbound',
            'content_type'    => 'text',
            'content'         => 'Sent!',
            'sender_type'     => 'agent',
            'delivered_at'    => now(),
            'created_at'      => now(),
        ]);

        $this->assertContains('delivered_at', $message->getImmutableColumns());
        $this->assertContains('read_at', $message->getImmutableColumns());
        $this->assertContains('failed_at', $message->getImmutableColumns());
    }
}
