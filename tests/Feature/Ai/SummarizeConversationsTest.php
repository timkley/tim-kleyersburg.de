<?php

declare(strict_types=1);

use App\Jobs\SummarizeConversations;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Gateway\TextGateway;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Gateway\FakeTextGateway;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\TextResponse;

function createConversation(array $attributes = []): AgentConversation
{
    return AgentConversation::create(array_merge([
        'id' => Str::uuid()->toString(),
        'title' => 'Test Conversation',
    ], $attributes));
}

function createMessage(AgentConversation $conversation, string $role, string $content, array $attributes = []): AgentConversationMessage
{
    return AgentConversationMessage::create(array_merge([
        'id' => Str::uuid()->toString(),
        'conversation_id' => $conversation->id,
        'agent' => 'chopper',
        'role' => $role,
        'content' => $content,
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '{}',
        'meta' => '{}',
    ], $attributes));
}

function createFakeTextProvider(array|Closure $responses = []): TextProvider
{
    $fakeGateway = new FakeTextGateway($responses);

    $provider = Mockery::mock(TextProvider::class);
    $provider->shouldReceive('textGateway')->andReturn($fakeGateway);
    $provider->shouldReceive('cheapestTextModel')->andReturn('fake-model');
    $provider->shouldReceive('name')->andReturn('fake');

    return $provider;
}

it('summarizes idle conversations with no summary', function () {
    $conversation = createConversation();

    createMessage($conversation, 'user', 'How do I deploy?', [
        'created_at' => now()->subHour(),
    ]);
    createMessage($conversation, 'assistant', 'Use CI/CD pipelines.', [
        'created_at' => now()->subMinutes(45),
    ]);

    $provider = createFakeTextProvider(['A conversation about deployment strategies.']);

    $job = new SummarizeConversations;
    $job->handle($provider);

    $conversation->refresh();

    expect($conversation->summary)->toBe('A conversation about deployment strategies.')
        ->and($conversation->summary_generated_at)->not->toBeNull();
});

it('skips active conversations with recent messages', function () {
    $conversation = createConversation();

    createMessage($conversation, 'user', 'Hello', [
        'created_at' => now()->subMinutes(10),
    ]);

    $provider = createFakeTextProvider(['Should not be called.']);

    $job = new SummarizeConversations;
    $job->handle($provider);

    $conversation->refresh();

    expect($conversation->summary)->toBeNull()
        ->and($conversation->summary_generated_at)->toBeNull();
});

it('re-summarizes when new messages exist after summary_generated_at', function () {
    $conversation = createConversation([
        'summary' => 'Old summary',
        'summary_generated_at' => now()->subHours(2),
    ]);

    createMessage($conversation, 'user', 'Original question', [
        'created_at' => now()->subHours(3),
    ]);
    createMessage($conversation, 'assistant', 'Original answer', [
        'created_at' => now()->subHours(3),
    ]);
    createMessage($conversation, 'user', 'Follow-up question about caching', [
        'created_at' => now()->subHour(),
    ]);
    createMessage($conversation, 'assistant', 'Caching answer', [
        'created_at' => now()->subMinutes(55),
    ]);

    $provider = createFakeTextProvider(['Updated summary including caching discussion.']);

    $job = new SummarizeConversations;
    $job->handle($provider);

    $conversation->refresh();

    expect($conversation->summary)->toBe('Updated summary including caching discussion.');
});

it('skips up-to-date conversations', function () {
    $conversation = createConversation([
        'summary' => 'Existing summary',
        'summary_generated_at' => now()->subHour(),
    ]);

    createMessage($conversation, 'user', 'Old message', [
        'created_at' => now()->subHours(2),
    ]);

    $provider = createFakeTextProvider(['Should not be called.']);

    $job = new SummarizeConversations;
    $job->handle($provider);

    $conversation->refresh();

    expect($conversation->summary)->toBe('Existing summary');
});

it('only sends user and assistant messages to the LLM', function () {
    $conversation = createConversation();

    createMessage($conversation, 'user', 'User question', [
        'created_at' => now()->subHour(),
    ]);
    createMessage($conversation, 'tool', 'tool_result_data', [
        'created_at' => now()->subMinutes(59),
    ]);
    createMessage($conversation, 'assistant', 'Assistant response', [
        'created_at' => now()->subMinutes(58),
    ]);

    $capturedMessages = null;

    $fakeGateway = Mockery::mock(TextGateway::class);
    $fakeGateway->shouldReceive('generateText')
        ->once()
        ->withArgs(function ($provider, $model, $instructions, $messages) use (&$capturedMessages) {
            $capturedMessages = $messages;

            return true;
        })
        ->andReturn(new TextResponse('Summary text.', new Usage, new Meta('fake', 'fake-model')));

    $provider = Mockery::mock(TextProvider::class);
    $provider->shouldReceive('textGateway')->andReturn($fakeGateway);
    $provider->shouldReceive('cheapestTextModel')->andReturn('fake-model');

    $job = new SummarizeConversations;
    $job->handle($provider);

    expect($capturedMessages)->toHaveCount(1)
        ->and($capturedMessages[0])->toBeInstanceOf(UserMessage::class)
        ->and($capturedMessages[0]->content)->toContain('User question')
        ->and($capturedMessages[0]->content)->toContain('Assistant response')
        ->and($capturedMessages[0]->content)->not->toContain('tool_result_data');
});
