<?php

namespace Tests\Unit\Omni;

use App\Exceptions\OmniException;
use App\Models\Omni\OmniAgent;
use App\Models\Omni\OmniConversation;
use App\Services\Omni\OmniConversationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class OmniConversationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OmniConversationService $service;
    protected LoggerInterface $logger;
    protected int $companyId = 1;
    protected int $agentId = 100;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->mock(LoggerInterface::class);
        $this->service = new OmniConversationService($this->logger);

        OmniAgent::factory()->create([
            'company_id' => $this->companyId,
            'id' => $this->agentId,
        ]);
    }

    /**
     * Test findOrCreate returns existing conversation.
     */
    public function test_find_or_create_returns_existing(): void
    {
        // Create initial conversation
        $initial = $this->service->findOrCreate([
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'channel_type' => 'web',
            'session_id' => 'session_xyz',
        ]);

        // Retrieve same conversation
        $retrieved = $this->service->findOrCreate([
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'channel_type' => 'web',
            'session_id' => 'session_xyz',
        ]);

        $this->assertEquals($initial->id, $retrieved->id);
        $this->assertFalse($retrieved->wasRecentlyCreated);
    }

    /**
     * Test findOrCreate creates new conversation with metadata.
     */
    public function test_find_or_create_with_metadata(): void
    {
        $metadata = ['source' => 'api', 'version' => '1.0'];

        $conversation = $this->service->findOrCreate([
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'customer_email' => 'test@example.com',
            'customer_name' => 'John',
            'metadata' => $metadata,
        ]);

        $this->assertEquals('test@example.com', $conversation->customer_email);
        $this->assertEquals('John', $conversation->customer_name);
        $this->assertEquals($metadata, $conversation->metadata);
    }

    /**
     * Test appendMessage creates message and increments counters.
     */
    public function test_append_message_increments_counters(): void
    {
        $conversation = $this->service->findOrCreate([
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
        ]);

        $this->service->appendMessage($conversation, [
            'role' => 'user',
            'message_type' => 'text',
            'content' => 'Hello',
        ]);

        $conversation->refresh();
        $this->assertEquals(1, $conversation->total_messages);
        $this->assertEquals(1, $conversation->user_messages);
        $this->assertEquals(0, $conversation->assistant_messages);

        $this->service->appendMessage($conversation, [
            'role' => 'assistant',
            'message_type' => 'text',
            'content' => 'Hi there',
        ]);

        $conversation->refresh();
        $this->assertEquals(2, $conversation->total_messages);
        $this->assertEquals(1, $conversation->user_messages);
        $this->assertEquals(1, $conversation->assistant_messages);
    }

    /**
     * Test appendMessage with voice confidence validation.
     */
    public function test_append_message_clamps_confidence(): void
    {
        $conversation = $this->service->findOrCreate([
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
        ]);

        // Test value > 1 is clamped to 1
        $message1 = $this->service->appendMessage($conversation, [
            'role' => 'user',
            'message_type' => 'voice_transcript',
            'content' => 'test',
            'voice_confidence' => 1.5,
        ]);

        $this->assertEquals(1.0, $message1->voice_confidence);

        // Test value < 0 is clamped to 0
        $message2 = $this->service->appendMessage($conversation, [
            'role' => 'user',
            'message_type' => 'voice_transcript',
            'content' => 'test',
            'voice_confidence' => -0.5,
        ]);

        $this->assertEquals(0.0, $message2->voice_confidence);
    }

    /**
     * Test validation: missing company_id throws exception.
     */
    public function test_throws_exception_missing_company_id(): void
    {
        $this->expectException(OmniException::class);
        $this->expectExceptionCode(400);

        $this->service->findOrCreate([
            'agent_id' => $this->agentId,
        ]);
    }

    /**
     * Test validation: invalid email throws exception.
     */
    public function test_throws_exception_invalid_email(): void
    {
        $this->expectException(OmniException::class);

        $this->service->findOrCreate([
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'customer_email' => 'not-an-email',
        ]);
    }

    /**
     * Test validation: empty message content throws exception.
     */
    public function test_throws_exception_empty_message(): void
    {
        $this->expectException(OmniException::class);

        $conversation = $this->service->findOrCreate([
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
        ]);

        $this->service->appendMessage($conversation, [
            'role' => 'user',
            // No content, voice_file_url, or media_url
        ]);
    }

    /**
     * Test validation: media size limit enforcement.
     */
    public function test_throws_exception_media_size_exceeded(): void
    {
        config(['omni.max_media_size_bytes' => 10000000]); // 10 MB

        $this->expectException(OmniException::class);

        $conversation = $this->service->findOrCreate([
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
        ]);

        $this->service->appendMessage($conversation, [
            'role' => 'user',
            'message_type' => 'media',
            'content' => 'Large file',
            'media_size_bytes' => 20000000, // Exceeds limit
        ]);
    }

    /**
     * Test logging on conversation creation.
     */
    public function test_logs_conversation_creation(): void
    {
        $this->logger->shouldReceive('info')
            ->once()
            ->with('Conversation created', \Mockery::on(function ($context) {
                return isset($context['conversation_id']) && isset($context['uuid']);
            }));

        $this->service->findOrCreate([
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
        ]);
    }

    /**
     * Test last_activity_at timestamp is updated.
     */
    public function test_updates_last_activity_timestamp(): void
    {
        $conversation = $this->service->findOrCreate([
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
        ]);

        $initialActivity = $conversation->last_activity_at;
        sleep(1);

        $this->service->appendMessage($conversation, [
            'role' => 'user',
            'content' => 'Hello',
        ]);

        $conversation->refresh();
        $this->assertGreaterThan($initialActivity, $conversation->last_activity_at);
    }
}
