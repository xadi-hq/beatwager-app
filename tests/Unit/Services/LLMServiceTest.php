<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\MessageContext;
use App\Models\Group;
use App\Services\LLMService;
use App\Services\LogService;
use App\Services\MessageTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class LLMServiceTest extends TestCase
{
    use RefreshDatabase;

    private LLMService $service;
    private MessageTrackingService $trackingService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock MessageTrackingService
        $this->trackingService = Mockery::mock(MessageTrackingService::class);
        $this->trackingService->shouldReceive('getRecentHistory')->andReturn([]);

        $this->service = new LLMService($this->trackingService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_generates_message_from_anthropic_api()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
            'bot_tone' => 'friendly and encouraging',
        ]);

        $context = new MessageContext(
            key: 'wager.joined',
            intent: 'Acknowledge user joining wager',
            requiredFields: ['username', 'wager_title'],
            data: [
                'username' => 'TestUser',
                'wager_title' => 'Will it rain?',
            ],
            group: $group
        );

        // Mock Anthropic API response
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['text' => '🎲 TestUser just joined "Will it rain?" - Bold move!']
                ]
            ], 200),
        ]);

        $result = $this->service->generate($context, $group);

        $this->assertIsString($result);
        $this->assertStringContainsString('TestUser', $result);
        $this->assertGreaterThan(20, strlen($result));

        // Verify API was called correctly
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.anthropic.com/v1/messages'
                && $request->hasHeader('x-api-key', 'test-key')
                && $request['model'] === 'claude-3-haiku-20240307';
        });
    }

    /** @test */
    public function it_generates_message_from_openai_api()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'openai',
            'llm_api_key' => 'sk-test-key',
        ]);

        $context = new MessageContext(
            key: 'wager.settled',
            intent: 'Announce wager settlement',
            requiredFields: ['winner', 'outcome'],
            data: [
                'winner' => 'Alice',
                'outcome' => 'yes',
            ]
        );

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '🏆 Alice wins! The outcome was yes.']]
                ]
            ], 200),
        ]);

        $result = $this->service->generate($context, $group);

        $this->assertIsString($result);
        $this->assertStringContainsString('Alice', $result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.openai.com/v1/chat/completions'
                && str_contains($request->header('Authorization')[0], 'Bearer sk-test-key')
                && $request['model'] === 'gpt-4o-mini';
        });
    }

    /** @test */
    public function it_uses_cached_response_on_subsequent_calls()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
        ]);

        $context = new MessageContext(
            key: 'wager.joined',
            intent: 'Test',
            requiredFields: [],
            data: ['test' => 'data']
        );

        // Set up HTTP fake once
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['text' => 'Generated message from cache test']]
            ], 200),
        ]);

        $result1 = $this->service->generate($context, $group);
        $this->assertEquals('Generated message from cache test', $result1);

        // Second call - should use cache (no API call)
        $result2 = $this->service->generate($context, $group);
        $this->assertEquals('Generated message from cache test', $result2);
        $this->assertEquals($result1, $result2);

        // Verify API was only called once
        Http::assertSentCount(1);
    }

    /** @test */
    public function it_throws_exception_on_api_failure()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
        ]);

        $context = new MessageContext(
            key: 'test',
            intent: 'Test',
            requiredFields: [],
            data: []
        );

        Http::fake([
            'api.anthropic.com/*' => Http::response(['error' => 'Rate limit exceeded'], 429),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Anthropic API error');

        $this->service->generate($context, $group);
    }

    /** @test */
    public function it_validates_response_length()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
        ]);

        $context = new MessageContext(
            key: 'test',
            intent: 'Test',
            requiredFields: [],
            data: []
        );

        // Response too short
        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['text' => 'Hi']] // Only 2 characters
            ], 200),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Response length invalid');

        $this->service->generate($context, $group);
    }

    /** @test */
    public function it_rejects_empty_responses()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
        ]);

        $context = new MessageContext(
            key: 'test',
            intent: 'Test',
            requiredFields: [],
            data: []
        );

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['text' => '                         ']] // Whitespace only (20+ chars)
            ], 200),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Empty response from LLM');

        $this->service->generate($context, $group);
    }

    /** @test */
    public function it_handles_requesty_provider()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'requesty',
            'llm_api_key' => 'requesty-key',
        ]);

        $context = new MessageContext(
            key: 'test',
            intent: 'Test',
            requiredFields: [],
            data: []
        );

        Http::fake([
            'router.requesty.ai/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Requesty generated message']]
                ]
            ], 200),
        ]);

        $result = $this->service->generate($context, $group);

        $this->assertEquals('Requesty generated message', $result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://router.requesty.ai/v1/chat/completions'
                && $request['model'] === 'openai/gpt-4o-mini'; // Requesty format
        });
    }

    /** @test */
    public function it_throws_exception_for_unknown_provider()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'unknown-provider',
            'llm_api_key' => 'test-key',
        ]);

        $context = new MessageContext(
            key: 'test',
            intent: 'Test',
            requiredFields: [],
            data: []
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown provider: unknown-provider');

        $this->service->generate($context, $group);
    }

    /** @test */
    public function it_respects_api_timeout()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
        ]);

        $context = new MessageContext(
            key: 'test',
            intent: 'Test',
            requiredFields: [],
            data: []
        );

        Http::fake([
            'api.anthropic.com/*' => function () {
                sleep(11); // Longer than 10s timeout
                return Http::response([], 200);
            },
        ]);

        $this->expectException(\Exception::class);

        $this->service->generate($context, $group);
    }

    /** @test */
    public function it_includes_group_description_in_system_prompt_for_social_messages()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
            'description' => 'We are a tight-knit group of coding enthusiasts',
            'bot_tone' => 'nerdy and playful',
        ]);

        // Use wager.settled which has max_words=50 (>30) so description is included
        $context = new MessageContext(
            key: 'wager.settled', // Social message type with max_words > 30
            intent: 'Test',
            requiredFields: ['title', 'outcome', 'winners', 'currency'],
            data: [
                'title' => 'Test Wager',
                'outcome' => 'Yes',
                'winners' => 'TestUser',
                'currency' => 'points'
            ]
        );

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['text' => 'Test response with enough characters to pass validation']]
            ], 200),
        ]);

        $this->service->generate($context, $group);

        // Verify system prompt includes description (check the request body)
        Http::assertSent(function ($request) use ($group) {
            // The description appears in the 'system' field of the request
            $hasSystemField = isset($request['system']);
            $containsDescription = $hasSystemField && str_contains($request['system'], $group->description);
            return $containsDescription;
        });
    }

    /** @test */
    public function it_excludes_group_description_for_functional_messages()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
            'description' => 'Test description',
        ]);

        $context = new MessageContext(
            key: 'wager.announced', // Functional message type
            intent: 'Test',
            requiredFields: [],
            data: []
        );

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['text' => 'Test response with enough characters here']]
            ], 200),
        ]);

        $this->service->generate($context, $group);

        // Verify system prompt excludes description
        Http::assertSent(function ($request) use ($group) {
            return !str_contains($request['system'], $group->description);
        });
    }

    /** @test */
    public function it_handles_nsfw_content_settings()
    {
        $groupNsfw = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
            'allow_nsfw' => true,
        ]);

        $groupSafe = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
            'allow_nsfw' => false,
        ]);

        $context = new MessageContext(
            key: 'test',
            intent: 'Test',
            requiredFields: [],
            data: []
        );

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['text' => 'Test response with sufficient length for validation']]
            ], 200),
        ]);

        // NSFW group
        $this->service->generate($context, $groupNsfw);
        Http::assertSent(function ($request) {
            return str_contains($request['system'], 'No content restrictions');
        });

        // Safe group
        $this->service->generate($context, $groupSafe);
        Http::assertSent(function ($request) {
            return str_contains($request['system'], 'Keep content appropriate for all ages');
        });
    }

    /** @test */
    public function it_uses_correct_language_in_system_prompt()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
            'language' => 'nl', // Dutch
        ]);

        $context = new MessageContext(
            key: 'test',
            intent: 'Test',
            requiredFields: [],
            data: []
        );

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['text' => 'Test antwoord met genoeg tekst voor validatie']]
            ], 200),
        ]);

        $this->service->generate($context, $group);

        Http::assertSent(function ($request) {
            return str_contains($request['system'], 'ALWAYS respond in Dutch');
        });
    }

    /** @test */
    public function cache_key_is_unique_per_context_and_settings()
    {
        $group1 = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
            'bot_tone' => 'friendly',
        ]);

        $group2 = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
            'bot_tone' => 'sarcastic', // Different tone
        ]);

        $context = new MessageContext(
            key: 'test',
            intent: 'Test',
            requiredFields: [],
            data: ['foo' => 'bar']
        );

        Http::fake([
            'api.anthropic.com/*' => Http::sequence()
                ->push(['content' => [['text' => 'Response 1 with enough length']]], 200)
                ->push(['content' => [['text' => 'Response 2 with enough length']]], 200),
        ]);

        $result1 = $this->service->generate($context, $group1);
        $result2 = $this->service->generate($context, $group2);

        // Different tones should produce different cache keys
        $this->assertNotEquals($result1, $result2);
        Http::assertSentCount(2); // Both called API (no cache hit)
    }

    /** @test */
    public function it_handles_custom_llm_instructions()
    {
        $group = Group::factory()->create([
            'llm_provider' => 'anthropic',
            'llm_api_key' => 'test-key',
        ]);

        $context = new MessageContext(
            key: 'test',
            intent: 'Test',
            requiredFields: [],
            data: [
                'llm_instructions' => 'Include exactly 5 emojis',
            ]
        );

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [['text' => 'Test response with custom instructions applied successfully']]
            ], 200),
        ]);

        $this->service->generate($context, $group);

        Http::assertSent(function ($request) {
            $messages = $request['messages'];
            $userPrompt = $messages[0]['content'];
            return str_contains($userPrompt, 'ADDITIONAL INSTRUCTIONS')
                && str_contains($userPrompt, 'Include exactly 5 emojis');
        });
    }
}
