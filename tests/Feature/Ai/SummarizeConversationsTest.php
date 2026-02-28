<?php

declare(strict_types=1);

use App\Jobs\SummarizeConversations;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use Illuminate\Support\Str;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Prompts\AgentPrompt;

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

it('summarizes idle conversations with no summary', function () {
    AnonymousAgent::fake(['A conversation about deployment strategies.']);

    $conversation = createConversation();

    createMessage($conversation, 'user', 'How do I deploy?', [
        'created_at' => now()->subHour(),
    ]);
    createMessage($conversation, 'assistant', 'Use CI/CD pipelines.', [
        'created_at' => now()->subMinutes(45),
    ]);

    (new SummarizeConversations)->handle();

    $conversation->refresh();

    expect($conversation->summary)->toBe('A conversation about deployment strategies.')
        ->and($conversation->summary_generated_at)->not->toBeNull();
});

it('skips active conversations with recent messages', function () {
    AnonymousAgent::fake(['Should not be called.']);

    $conversation = createConversation();

    createMessage($conversation, 'user', 'Hello', [
        'created_at' => now()->subMinutes(10),
    ]);

    (new SummarizeConversations)->handle();

    $conversation->refresh();

    expect($conversation->summary)->toBeNull()
        ->and($conversation->summary_generated_at)->toBeNull();

    AnonymousAgent::assertNeverPrompted();
});

it('re-summarizes when new messages exist after summary_generated_at', function () {
    AnonymousAgent::fake(['Updated summary including caching discussion.']);

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

    (new SummarizeConversations)->handle();

    $conversation->refresh();

    expect($conversation->summary)->toBe('Updated summary including caching discussion.');
});

it('skips up-to-date conversations', function () {
    AnonymousAgent::fake(['Should not be called.']);

    $conversation = createConversation([
        'summary' => 'Existing summary',
        'summary_generated_at' => now()->subHour(),
    ]);

    createMessage($conversation, 'user', 'Old message', [
        'created_at' => now()->subHours(2),
    ]);

    (new SummarizeConversations)->handle();

    $conversation->refresh();

    expect($conversation->summary)->toBe('Existing summary');

    AnonymousAgent::assertNeverPrompted();
});

it('skips summarization when conversation has only tool messages', function () {
    AnonymousAgent::fake(['Should not be called.']);

    $conversation = createConversation();

    createMessage($conversation, 'tool', 'tool_result_data', [
        'created_at' => now()->subHour(),
    ]);

    (new SummarizeConversations)->handle();

    $conversation->refresh();

    expect($conversation->summary)->toBeNull()
        ->and($conversation->summary_generated_at)->toBeNull();
});

it('only sends user and assistant messages to the LLM', function () {
    AnonymousAgent::fake(['Summary text.']);

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

    (new SummarizeConversations)->handle();

    AnonymousAgent::assertPrompted(function (AgentPrompt $prompt) {
        return str_contains($prompt->prompt, 'User question')
            && str_contains($prompt->prompt, 'Assistant response')
            && ! str_contains($prompt->prompt, 'tool_result_data');
    });
});
