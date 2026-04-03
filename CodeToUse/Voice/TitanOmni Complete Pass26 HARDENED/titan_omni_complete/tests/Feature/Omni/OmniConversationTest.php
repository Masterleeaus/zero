<?php

namespace Tests\Feature\Omni;

use App\Models\Omni\OmniAgent;
use App\Models\Omni\OmniConversation;
use App\Models\Omni\OmniMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OmniConversationTest extends TestCase
{
    use RefreshDatabase;

    protected int $companyId = 1;
    protected int $agentId = 100;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestAgent();
    }

    protected function createTestAgent(): OmniAgent
    {
        return OmniAgent::factory()->create([
            'company_id' => $this->companyId,
            'id' => $this->agentId,
        ]);
    }

    /**
     * Test creating a new conversation via POST endpoint.
     */
    public function test_can_create_conversation(): void
    {
        $response = $this->postJson('/dashboard/user/omni/conversation', [
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'channel_type' => 'web',
            'customer_email' => 'test@example.com',
            'customer_name' => 'John Doe',
            'message' => 'Hello, how can I help?',
        ])->assertStatus(200);

        $response->assertJsonStructure([
            'conversation_id',
            'reply',
            'mode',
        ]);

        $this->assertDatabaseHas('omni_conversations', [
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'channel_type' => 'web',
            'customer_email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('omni_messages', [
            'role' => 'user',
            'content' => 'Hello, how can I help?',
        ]);
    }

    /**
     * Test that subsequent messages append to same conversation.
     */
    public function test_messages_append_to_existing_conversation(): void
    {
        // First message
        $response1 = $this->postJson('/dashboard/user/omni/conversation', [
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'channel_type' => 'web',
            'session_id' => 'session_xyz',
            'message' => 'First message',
        ]);

        $conversationId = $response1->json('conversation_id');

        // Second message in same session
        $response2 = $this->postJson('/dashboard/user/omni/conversation', [
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'channel_type' => 'web',
            'session_id' => 'session_xyz',
            'message' => 'Second message',
        ]);

        $this->assertEquals($conversationId, $response2->json('conversation_id'));

        $conversation = OmniConversation::find($conversationId);
        $this->assertEquals(4, $conversation->total_messages); // 2 user + 2 assistant
        $this->assertEquals(2, $conversation->user_messages);
        $this->assertEquals(2, $conversation->assistant_messages);
    }

    /**
     * Test retrieving conversations list.
     */
    public function test_can_list_conversations(): void
    {
        // Create multiple conversations
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/dashboard/user/omni/conversation', [
                'company_id' => $this->companyId,
                'agent_id' => $this->agentId,
                'message' => "Conversation {$i}",
            ]);
        }

        $response = $this->getJson('/dashboard/user/omni/conversations?company_id=' . $this->companyId)
            ->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'uuid',
                    'agent_id',
                    'status',
                    'channel_type',
                    'total_messages',
                    'last_activity_at',
                    'messages',
                ]
            ],
            'meta' => [
                'total',
                'per_page',
                'current_page',
            ]
        ]);

        $this->assertEquals(3, $response->json('meta.total'));
    }

    /**
     * Test voice message with transcription.
     */
    public function test_can_append_voice_message(): void
    {
        $response = $this->postJson('/dashboard/user/omni/conversation', [
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'channel_type' => 'voice',
            'message' => 'Reset my password',
            'voice_file_url' => 's3://bucket/audio.wav',
            'voice_duration_seconds' => 5,
            'voice_model' => 'eleven_labs',
            'voice_transcript' => 'Reset my password',
            'voice_confidence' => 0.95,
        ])->assertStatus(200);

        $this->assertDatabaseHas('omni_messages', [
            'role' => 'user',
            'message_type' => 'voice_transcript',
            'voice_confidence' => 0.95,
        ]);
    }

    /**
     * Test media message attachment.
     */
    public function test_can_append_media_message(): void
    {
        $response = $this->postJson('/dashboard/user/omni/conversation', [
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'channel_type' => 'web',
            'message' => 'Here is my receipt',
            'media_url' => 's3://bucket/receipt.pdf',
            'media_type' => 'application/pdf',
            'media_size_bytes' => 1024000,
        ])->assertStatus(200);

        $this->assertDatabaseHas('omni_messages', [
            'role' => 'user',
            'media_url' => 's3://bucket/receipt.pdf',
            'media_type' => 'application/pdf',
        ]);
    }

    /**
     * Test validation: missing required fields.
     */
    public function test_rejects_missing_company_id(): void
    {
        $this->postJson('/dashboard/user/omni/conversation', [
            'agent_id' => $this->agentId,
            'message' => 'Hello',
        ])->assertStatus(422);
    }

    /**
     * Test validation: invalid email format.
     */
    public function test_rejects_invalid_email(): void
    {
        $this->postJson('/dashboard/user/omni/conversation', [
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'customer_email' => 'not-an-email',
            'message' => 'Hello',
        ])->assertStatus(422);
    }

    /**
     * Test rate limiting on conversation endpoint.
     */
    public function test_rate_limit_conversation_store(): void
    {
        // This assumes rate limiting is enabled in config
        config(['omni.rate_limits.conversation.store' => ['requests' => 2, 'decay' => 1]]);

        // First request - OK
        $this->postJson('/dashboard/user/omni/conversation', [
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'message' => 'Message 1',
        ])->assertStatus(200);

        // Second request - OK
        $this->postJson('/dashboard/user/omni/conversation', [
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'message' => 'Message 2',
        ])->assertStatus(200);

        // Third request - Rate limited
        $this->postJson('/dashboard/user/omni/conversation', [
            'company_id' => $this->companyId,
            'agent_id' => $this->agentId,
            'message' => 'Message 3',
        ])->assertStatus(429)
            ->assertJsonStructure([
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'retry_after_seconds',
                ]
            ]);
    }
}
